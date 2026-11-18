<?php

namespace App\Support;

/**
 * Health details onboarding definition (Disabilities, Food Allergies, Medical Conditions).
 *
 * Wording, order and options follow the confirmed visual mockup
 * ("Health Profile Intake Flow"); field names follow the Health Profile Intake Spec.
 *
 * Rules agreed with the client:
 *  - Every group is optional: submitting nothing is a valid, completed onboarding.
 *  - Once a category is checked, its safety questions are required.
 *  - Free-text fields are optional and capped at 300 characters.
 *  - excluded_exercises only accepts predefined tags, never free typing.
 */
class HealthDetails
{
    public const NOTES_MAX = 300;

    /**
     * API (client format) mapping: internal group => profile key in the JSON
     * and the array that lists the checked categories.
     */
    public const PROFILES = [
        'disabilities' => ['profile' => 'accessibility_profile', 'list' => 'categories'],
        'food_allergies' => ['profile' => 'dietary_profile', 'list' => 'allergens'],
        'medical_conditions' => ['profile' => 'medical_profile', 'list' => 'conditions'],
    ];

    /**
     * Predefined movement tags members can exclude ("Any specific movements or exercises to avoid?").
     * Placeholder list until the exercise library is tagged; keep slugs stable once in use.
     */
    public const EXCLUDABLE_EXERCISE_TAGS = [
        'deep_squats' => 'Deep squats',
        'lunges' => 'Lunges',
        'overhead_press' => 'Overhead press',
        'jumping' => 'Jumping',
        'running' => 'Running',
        'burpees' => 'Burpees',
        'push_ups' => 'Push-ups',
        'planks' => 'Planks',
        'deadlifts' => 'Deadlifts',
        'twisting' => 'Twisting movements',
        'kneeling' => 'Kneeling',
        'lying_on_floor' => 'Lying on the floor',
    ];

    public static function groups(): array
    {
        $yesNo = 'boolean';

        return [
            'disabilities' => [
                'title' => 'Disabilities',
                'categories' => [
                    'mobility' => [
                        'label' => 'Mobility',
                        'headline' => 'A bit more about mobility',
                        'subhead' => 'This helps us pick the right positions and equipment for you.',
                        'questions' => [
                            ['field' => 'uses_mobility_aid', 'label' => 'Do you use a wheelchair or another mobility aid?', 'type' => $yesNo, 'required' => true],
                            ['field' => 'floor_transition_capable', 'label' => 'Can you get down to the floor and back up without help?', 'type' => $yesNo, 'required' => true],
                            ['field' => 'standing_tolerance', 'label' => 'How long can you comfortably stand?', 'type' => 'single_select', 'required' => true, 'options' => [
                                'not_long' => 'Not long',
                                'a_few_minutes' => 'A few minutes',
                                'as_long_as_i_need' => 'As long as I need',
                            ]],
                            ['field' => 'limited_rom_joints', 'label' => 'Any joints with limited range of motion?', 'type' => 'multi_select', 'required' => false, 'options' => [
                                'shoulder' => 'Shoulder',
                                'knee' => 'Knee',
                                'hip' => 'Hip',
                                'ankle' => 'Ankle',
                                'spine' => 'Spine',
                                'wrist_elbow' => 'Wrist / elbow',
                            ]],
                        ],
                    ],
                    'visual' => [
                        'label' => 'Visual',
                        'headline' => 'A bit more about your vision',
                        'subhead' => 'So we know whether to lean on visuals or talk you through it.',
                        'questions' => [
                            ['field' => 'vision_type', 'label' => 'How would you describe your vision?', 'type' => 'single_select', 'required' => true, 'options' => [
                                'low_vision' => 'Low vision',
                                'light_sensitive' => 'Light-sensitive',
                                'blind' => 'Blind',
                                'color_blind' => 'Color-blind',
                            ]],
                            ['field' => 'needs_audio_cues', 'label' => 'Do you rely on spoken cues rather than on-screen demos?', 'type' => $yesNo, 'required' => true],
                            ['field' => 'needs_high_contrast_ui', 'label' => 'Would larger text or higher contrast help?', 'type' => $yesNo, 'required' => true],
                        ],
                    ],
                    'hearing' => [
                        'label' => 'Hearing',
                        'headline' => 'A bit more about hearing',
                        'subhead' => 'So audio cues get a visual or vibration backup when you need one.',
                        'questions' => [
                            ['field' => 'needs_visual_cues', 'label' => 'Would you prefer visual or vibration cues instead of audio for timers and rep counts?', 'type' => $yesNo, 'required' => true],
                            ['field' => 'needs_captions', 'label' => 'Do you need captions on video content?', 'type' => $yesNo, 'required' => true],
                        ],
                    ],
                    'cognitive' => [
                        'label' => 'Cognitive',
                        'headline' => 'A bit more about pacing',
                        'subhead' => 'So instructions land at the right speed and level of detail for you.',
                        'questions' => [
                            ['field' => 'simplified_instructions', 'label' => 'Do you prefer single-step instructions over multi-step routines?', 'type' => $yesNo, 'required' => true],
                            ['field' => 'extended_rest_periods', 'label' => 'Does a slower pace with longer rests between exercises help?', 'type' => $yesNo, 'required' => true],
                            ['field' => 'avoid_high_stimulation', 'label' => 'Do fast-paced routines with lots of switching feel overwhelming?', 'type' => $yesNo, 'required' => true],
                        ],
                    ],
                    'other' => [
                        'label' => 'Other',
                        'description' => 'Tell us in your own words',
                        'eyebrow' => 'Disabilities · Other',
                        'headline' => 'Anything else we should know?',
                        'subhead' => 'Optional — a sentence or two is plenty.',
                        'questions' => [
                            ['field' => 'other_condition_notes', 'label' => 'Tell us about anything else that affects how you exercise', 'type' => 'text', 'required' => false, 'max' => self::NOTES_MAX],
                            ['field' => 'excluded_exercises', 'label' => 'Any specific movements or exercises to avoid?', 'type' => 'tags', 'required' => false, 'options' => self::EXCLUDABLE_EXERCISE_TAGS],
                        ],
                    ],
                ],
            ],
            'food_allergies' => [
                'title' => 'Food Allergies',
                // All checked allergies share one follow-up screen.
                'headline' => 'A bit more about your allergies',
                'subhead' => 'So we know when to just deprioritize an ingredient vs. rule it out completely.',
                'categories' => [
                    'dairy' => self::allergen('Dairy'),
                    'nuts' => self::allergen('Nuts'),
                    'gluten' => self::allergen('Gluten'),
                    'other' => [
                        'label' => 'Other',
                        'questions' => [
                            ['field' => 'other_allergy_notes', 'label' => 'Any other allergy or intolerance?', 'type' => 'text', 'required' => false, 'max' => self::NOTES_MAX],
                        ],
                    ],
                ],
            ],
            'medical_conditions' => [
                'title' => 'Medical Conditions',
                // Diabetes + Other share one follow-up screen.
                'headline' => 'A bit more about your health',
                'subhead' => 'So we can build in the right precautions.',
                'categories' => [
                    'diabetes' => [
                        'label' => 'Diabetes',
                        'questions' => [
                            ['field' => 'on_glucose_medication', 'label' => 'Diabetes — on insulin or another blood-sugar medication?', 'type' => $yesNo, 'required' => true],
                        ],
                    ],
                    'other' => [
                        'label' => 'Other',
                        'questions' => [
                            ['field' => 'other_condition_notes', 'label' => 'Tell us in a sentence.', 'type' => 'text', 'required' => false, 'max' => self::NOTES_MAX],
                        ],
                    ],
                ],
            ],
        ];
    }

    private static function allergen(string $name): array
    {
        return [
            'label' => $name,
            'questions' => [
                ['field' => 'severity', 'label' => "{$name} — how severe?", 'type' => 'single_select', 'required' => true, 'options' => [
                    'mild' => 'Mild',
                    'avoid' => 'Avoid',
                    'severe' => 'Severe',
                ]],
            ],
        ];
    }

    /**
     * Definition for the app, with options as [{value, label}] lists so order is preserved in JSON.
     */
    public static function forApi(): array
    {
        $out = [];
        foreach (self::groups() as $groupKey => $group) {
            $categories = [];
            foreach ($group['categories'] as $catKey => $cat) {
                $cat['key'] = $catKey;
                $cat['questions'] = array_map(function ($q) {
                    if (isset($q['options'])) {
                        $q['options'] = collect($q['options'])
                            ->map(fn ($label, $value) => ['value' => $value, 'label' => $label])
                            ->values()->all();
                    }
                    return $q;
                }, $cat['questions']);
                $categories[] = $cat;
            }
            $group['key'] = $groupKey;
            $group['profile'] = self::PROFILES[$groupKey]['profile'];
            $group['list_field'] = self::PROFILES[$groupKey]['list'];
            $group['categories'] = $categories;
            $out[] = $group;
        }
        return $out;
    }

    /**
     * Validation rules for a submission in the client format:
     * { "accessibility_profile": { "categories": [...], "mobility": {...}, "other_condition_notes": "...", ... },
     *   "dietary_profile": { "allergens": [...], "nuts": {...}, "other_allergy_notes": "..." },
     *   "medical_profile": { "conditions": [...], "diabetes": {...}, "other_condition_notes": "..." } }
     * Derived flags in the input are ignored: the server computes them.
     */
    public static function rules(array $input): array
    {
        $rules = [];

        foreach (self::groups() as $groupKey => $group) {
            $profile = self::PROFILES[$groupKey]['profile'];
            $list = self::PROFILES[$groupKey]['list'];
            $profileInput = is_array($input[$profile] ?? null) ? $input[$profile] : [];

            $rules[$profile] = ['sometimes', 'nullable', 'array'];
            $rules["{$profile}.{$list}"] = ['sometimes', 'nullable', 'array'];
            $rules["{$profile}.{$list}.*"] = ['string', 'distinct', 'in:' . implode(',', array_keys($group['categories']))];

            $checked = self::checkedCategories($groupKey, $profileInput);

            foreach ($group['categories'] as $catKey => $cat) {
                if ($catKey === 'other') {
                    // "Other" fields sit directly on the profile and are always optional.
                    foreach ($cat['questions'] as $q) {
                        $rules += self::questionRules("{$profile}.{$q['field']}", $q, false);
                    }
                    continue;
                }

                if (!in_array($catKey, $checked, true)) {
                    continue;
                }

                $rules["{$profile}.{$catKey}"] = ['required', 'array'];
                foreach ($cat['questions'] as $q) {
                    $rules += self::questionRules("{$profile}.{$catKey}.{$q['field']}", $q, $q['required']);
                }
            }
        }

        return $rules;
    }

    private static function questionRules(string $path, array $q, bool $required): array
    {
        $presence = $required ? 'required' : 'nullable';

        switch ($q['type']) {
            case 'boolean':
                return [$path => [$presence, 'boolean']];
            case 'single_select':
                return [$path => [$presence, 'string', 'in:' . implode(',', array_keys($q['options']))]];
            case 'multi_select':
            case 'tags':
                return [
                    $path => [$presence, 'array'],
                    "{$path}.*" => ['string', 'distinct', 'in:' . implode(',', array_keys($q['options']))],
                ];
            case 'text':
                return [$path => [$presence, 'string', 'max:' . ($q['max'] ?? self::NOTES_MAX)]];
        }

        return [];
    }

    /**
     * Categories the member checked in one profile, in definition order.
     * "Other" counts as checked when it is listed or any of its fields has a value.
     */
    public static function checkedCategories(string $groupKey, array $profileInput): array
    {
        $listed = $profileInput[self::PROFILES[$groupKey]['list']] ?? [];
        $listed = is_array($listed) ? $listed : [];

        $checked = [];
        foreach (self::groups()[$groupKey]['categories'] as $catKey => $cat) {
            $isChecked = in_array($catKey, $listed, true);

            if ($catKey === 'other' && !$isChecked) {
                foreach ($cat['questions'] as $q) {
                    $value = $profileInput[$q['field']] ?? null;
                    if ((is_string($value) && trim($value) !== '') || (is_array($value) && count($value) > 0)) {
                        $isChecked = true;
                        break;
                    }
                }
            }

            if ($isChecked) {
                $checked[] = $catKey;
            }
        }

        return $checked;
    }

    /**
     * Client-format input => [group => [category => answers]] for the checked categories.
     */
    public static function toAnswers(array $input): array
    {
        $out = [];

        foreach (self::groups() as $groupKey => $group) {
            $profileInput = $input[self::PROFILES[$groupKey]['profile']] ?? [];
            $profileInput = is_array($profileInput) ? $profileInput : [];
            $out[$groupKey] = [];

            foreach (self::checkedCategories($groupKey, $profileInput) as $catKey) {
                $raw = $catKey === 'other' ? $profileInput : ($profileInput[$catKey] ?? []);
                $out[$groupKey][$catKey] = self::cleanAnswers($groupKey, $catKey, is_array($raw) ? $raw : []);
            }
        }

        return $out;
    }

    /**
     * [group => [category => answers]] (active rows) => client-format profiles.
     */
    public static function toProfiles(array $answers): array
    {
        $out = [];

        foreach (self::groups() as $groupKey => $group) {
            $active = $answers[$groupKey] ?? [];
            $checked = array_values(array_filter(array_keys($group['categories']), fn ($k) => isset($active[$k])));
            $profile = [self::PROFILES[$groupKey]['list'] => $checked];

            foreach ($checked as $catKey) {
                if ($catKey !== 'other') {
                    $profile[$catKey] = $active[$catKey];
                }
            }

            // "Other" fields are always present on the profile so the app can rely on the keys.
            foreach (self::cleanAnswers($groupKey, 'other', $active['other'] ?? []) as $field => $value) {
                $profile[$field] = $value;
            }

            $out[self::PROFILES[$groupKey]['profile']] = $profile;
        }

        return $out;
    }

    /**
     * Keep only known fields for a category and normalise their types.
     */
    public static function cleanAnswers(string $groupKey, string $catKey, ?array $answers): array
    {
        $answers = $answers ?? [];
        $clean = [];

        foreach (self::groups()[$groupKey]['categories'][$catKey]['questions'] as $q) {
            $value = $answers[$q['field']] ?? null;

            switch ($q['type']) {
                case 'boolean':
                    $clean[$q['field']] = $value === null ? null : filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    break;
                case 'multi_select':
                case 'tags':
                    $clean[$q['field']] = array_values(array_unique(is_array($value) ? $value : []));
                    break;
                case 'text':
                    $value = is_string($value) ? trim($value) : null;
                    $clean[$q['field']] = $value === '' ? null : $value;
                    break;
                default:
                    $clean[$q['field']] = $value;
            }
        }

        return $clean;
    }
}
