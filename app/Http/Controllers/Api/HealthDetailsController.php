<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MemberHealthDetail;
use App\Support\HealthDetails;
use App\Support\HealthFlags;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class HealthDetailsController extends Controller
{
    /**
     * Screen definition: groups, categories, questions and options, in mockup order.
     */
    public function options()
    {
        return response()->json([
            'status' => true,
            'message' => 'Health details options retrieved successfully',
            'data' => HealthDetails::forApi(),
        ], 200);
    }

    /**
     * The member's current (active) health details, used to pre-fill My Profile.
     */
    public function show()
    {
        $member = Auth::user();

        return response()->json([
            'status' => true,
            'message' => 'Health details retrieved successfully',
            'data' => $this->present($member),
        ], 200);
    }

    /**
     * Save health details. Used by onboarding (after register) and by My Profile edits.
     *
     * Body (client format): { "accessibility_profile": { "categories": [...], ... },
     *                         "dietary_profile": { "allergens": [...], ... },
     *                         "medical_profile": { "conditions": [...], ... } }
     *
     * Categories listed = checked. Every previously checked category that is not listed is
     * marked inactive (answers kept). An empty body is valid and means "nothing applies".
     * Derived flags sent by the app are ignored; the server computes them.
     */
    public function store(Request $request)
    {
        $member = Auth::user();
        if (!$member) {
            return response()->json(['status' => false, 'message' => 'Your session has expired. Please log in again.'], 401);
        }

        $input = $request->all();
        $validator = Validator::make($input, HealthDetails::rules($input), HealthDetails::messages($input));

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                // First problem, ready to show as a toast; `errors` maps each field to its message.
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::transaction(function () use ($member, $input) {
                $existing = MemberHealthDetail::where('member_id', $member->member_id)->get()
                    ->keyBy(fn ($row) => "{$row->group}.{$row->category}");

                $checked = [];
                foreach (HealthDetails::toAnswers($input) as $groupKey => $categories) {
                    foreach ($categories as $catKey => $answers) {
                        $checked["{$groupKey}.{$catKey}"] = true;

                        MemberHealthDetail::updateOrCreate(
                            ['member_id' => $member->member_id, 'group' => $groupKey, 'category' => $catKey],
                            ['answers' => $answers, 'is_active' => true]
                        );
                    }
                }

                // Unchecked categories become inactive instead of being deleted.
                foreach ($existing as $key => $row) {
                    if (!isset($checked[$key]) && $row->is_active) {
                        $row->update(['is_active' => false]);
                    }
                }

                if ($member->health_details_completed_at === null) {
                    $member->health_details_completed_at = now();
                    $member->save();
                }
            });

            return response()->json([
                'status' => true,
                'message' => 'Health details saved successfully',
                'data' => $this->present($member->fresh()),
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Health details save failed', ['member_id' => $member->member_id, 'exception' => $th]);

            return response()->json([
                'status' => false,
                'message' => "We couldn't save your details right now. Please try again in a moment.",
            ], 500);
        }
    }

    /**
     * Client format: the three profiles plus flags derived from the current answers.
     */
    private function present($member): array
    {
        $answers = [];
        MemberHealthDetail::where('member_id', $member->member_id)
            ->where('is_active', true)
            ->get()
            ->each(function ($row) use (&$answers) {
                $answers[$row->group][$row->category] = $row->answers ?? [];
            });
        return ['health_details_completed' => $member->health_details_completed]
            + HealthDetails::toProfiles($answers)
            + [
                // (object) keeps an empty flag set as {} in JSON.
                'derived_workout_flags' => (object) HealthFlags::workout($answers),
                'derived_meal_flags' => (object) HealthFlags::meal($answers),
            ];
    }
}
