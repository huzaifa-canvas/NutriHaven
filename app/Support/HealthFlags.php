<?php

namespace App\Support;

/**
 * Derives workout and meal flags from a member's health answers.
 *
 * Flags are never stored or edited directly: they are recomputed from the
 * current answers every time, so a change to an answer (or to these rules)
 * takes effect immediately. Only flags that apply are returned.
 *
 * NOTE: the exact answer => flag rules below are our reading of the Health
 * Profile Intake Spec and still need client confirmation.
 *
 * @param array $answers [group => [category => answers]] for active categories
 */
class HealthFlags
{
    public static function workout(array $answers): array
    {
        $flags = [];
        $mobility = $answers['disabilities']['mobility'] ?? null;
        $visual = $answers['disabilities']['visual'] ?? null;
        $hearing = $answers['disabilities']['hearing'] ?? null;
        $cognitive = $answers['disabilities']['cognitive'] ?? null;
        $diabetes = $answers['medical_conditions']['diabetes'] ?? null;

        if ($mobility) {
            if (($mobility['standing_tolerance'] ?? null) === 'not_long') {
                $flags['seated_exercises_only'] = true;
            }
            if (($mobility['floor_transition_capable'] ?? null) === false) {
                $flags['no_floor_exercises'] = true;
            }
            if (!empty($mobility['limited_rom_joints'])) {
                $flags['avoid_joints'] = array_values($mobility['limited_rom_joints']);
            }
            if (($mobility['uses_mobility_aid'] ?? null) === true) {
                $flags['no_high_impact'] = true;
            }
        }

        if ($visual) {
            if (($visual['needs_audio_cues'] ?? null) === true || ($visual['vision_type'] ?? null) === 'blind') {
                $flags['audio_first_instructions'] = true;
            }
            if (($visual['needs_high_contrast_ui'] ?? null) === true) {
                $flags['high_contrast_ui'] = true;
            }
            if (($visual['vision_type'] ?? null) === 'light_sensitive') {
                $flags['avoid_flashing_visuals'] = true;
            }
        }

        if ($hearing) {
            if (($hearing['needs_visual_cues'] ?? null) === true) {
                $flags['visual_timer_required'] = true;
            }
            if (($hearing['needs_captions'] ?? null) === true) {
                $flags['captions_required'] = true;
            }
        }

        if ($cognitive) {
            if (($cognitive['simplified_instructions'] ?? null) === true) {
                $flags['simple_routine_structure'] = true;
            }
            if (($cognitive['extended_rest_periods'] ?? null) === true) {
                $flags['slow_pace'] = true;
            }
            if (($cognitive['avoid_high_stimulation'] ?? null) === true) {
                $flags['minimal_transitions'] = true;
            }
        }

        if ($diabetes && ($diabetes['on_glucose_medication'] ?? null) === true) {
            $flags['monitor_blood_sugar'] = true;
        }

        return $flags;
    }

    public static function meal(array $answers): array
    {
        $flags = [];

        foreach (['dairy', 'nuts', 'gluten'] as $allergen) {
            $severity = $answers['food_allergies'][$allergen]['severity'] ?? null;

            if ($severity === 'severe') {
                $flags["exclude_{$allergen}_strict"] = true;
            } elseif ($severity === 'mild' || $severity === 'avoid') {
                $flags["deprioritize_{$allergen}"] = true;
            }
        }

        return $flags;
    }
}
