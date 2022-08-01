<?php

namespace App\Classes;

class MockUp
{
    private static $types = [
        [
            'name' => 'text_area',
            'answer_min' => 0
        ],
        [
            'name'=> 'radio',
            'answer_min'=> 2
        ],
        [
            'name'=> 'checkbox',
            'answer_min'=> 1
        ],
        [
            'name'=> 'date',
            'answer_min'=> 0
        ],
        [
            'name'=> 'hour',
            'answer_min'=> 0
        ],
        [
            'name'=> 'text',
            'answer_min'=> 0
        ],
        [
            'name'=> 'select',
            'answer_min'=> 1
        ],
        [
            'name'=> 'number',
            'answer_min'=> 0
        ],
        [
            'name'=> 'file',
            'answer_min'=> 0
        ],
        [
            'name'=> 'matrix_radio',
            'answer_min'=> 1
        ],
        [
            'name'=> 'matrix_checkbox',
            'answer_min'=> 1
        ],
        [
            'name' => 'satisfaction',
            'answer_min' => 3
        ],
        [
            'name' => 'net_promoter_score',
            'answer_min' => 10
        ]
    ];

    private static $mock_up = [
        1 => [
            'id' => 1,
            'image' => null,
            'name' => [
                'fr' => ''
            ],
            'answers' => [],
            'type' => 'text_area',
            'answer_min' => 0
        ],
        2 => [
            'id' => 2,
            'image' => null,
            'name' => [
                'fr' => ''
            ],
            'answers' => [
                [
                    'id' => 1,
                    'image' => null,
                    'order' => 0,
                    'value' => 100,
                    'name' => [
                        'fr' => 'Oui'
                    ]
                ],
                [
                    'id' => 2,
                    'image' => null,
                    'order' => 1,
                    'value' => 0,
                    'name' => [
                        'fr' => 'Non'
                    ]
                ]
            ],
            'type' => 'radio',
            'answer_min' => 2
        ],
        3 => [
            'id' => 3,
            'image' => null,
            'name' => [
                'fr' => ''
            ],
            'answers' => [
                [
                    'id' => 3,
                    'image' => null,
                    'order' => 0,
                    'value' => 1,
                    'name' => [
                        'fr' => 'Réponse n°1'
                    ]
                ]
            ],
            'type' => 'checkbox',
            'answer_min' => 1
        ],
        4 => [
            'id' => 4,
            'image' => null,
            'name' => [
                'fr' => ''
            ],
            'answers' => [],
            'type' => 'date',
            'answer_min' => 0
        ],
        5 => [
            'id' => 5,
            'image' => null,
            'name' => [
                'fr' => ''
            ],
            'answers' => [],
            'type' => 'hour',
            'answer_min' => 0
        ],
        6 => [
            'id' => 6,
            'image' => null,
            'name' => [
                'fr' => ''
            ],
            'answers' => [],
            'type' => 'text',
            'answer_min' => 0
        ],
        7 => [
            'id' => 7,
            'image' => null,
            'name' => [
                'fr' => ''
            ],
            'answers' => [
                [
                    'id' => 4,
                    'image' => null,
                    'order' => 0,
                    'value' => 0,
                    'name' => [
                        'fr' => 'Oui'
                    ]
                ],
                [
                    'id' => 5,
                    'image' => null,
                    'order' => 1,
                    'value' => 100,
                    'name' => [
                        'fr' => 'Non'
                    ]
                ]
            ],
            'type' => 'select',
            'answer_min' => 2
        ],
        8 => [
            'id' => 8,
            'image' => null,
            'name' => [
                'fr' => ''
            ],
            'answers' => [],
            'type' => 'number',
            'answer_min'=> 0
        ],
        9 => [
            'id' => 9,
            'image' => null,
            'answers' => [],
            'name' => [
                'fr' => ''
            ],
            'type' => 'file',
            'answer_min' => 0
        ],
        10 => [
            'id' => 10,
            'image' => null,
            'name' => [
                'fr' => ''
            ],
            'answers' => [
                [
                    'id' => 6,
                    'image' => null,
                    'order' => 0,
                    'value' => 1,
                    'name' => [
                        'fr' => 'Réponse n°1'
                    ]
                ],
                [
                    'id' => 7,
                    'image' => null,
                    'order' => 1,
                    'value' => 1,
                    'name' => [
                        'fr' => 'Réponse n°2'
                    ]
                ]
            ],
            'cols' => [
                [
                    'id' => 1,
                    'order' => 0,
                    'name' => [
                        'fr' => 'Critère n°1'
                    ]
                ],
                [
                    'id' => 2,
                    'order' => 1,
                    'name' => [
                        'fr' => 'Critère n°2'
                    ]
                ]
            ],
            'type' => 'matrix_radio',
            'answer_min' => 0
        ],
        11 => [
            'id' => 11,
            'image' => null,
            'name' => [
                'fr' => ''
            ],
            'answers' => [
                [
                    'id' => 8,
                    'image' => null,
                    'order' => 0,
                    'value' => 1,
                    'name' => [
                        'fr' => 'Réponse n°1'
                    ]
                ],
                [
                    'id' => 9,
                    'image' => null,
                    'order' => 1,
                    'value' => 1,
                    'name' => [
                        'fr' => 'Réponse n°2'
                    ]
                ]
            ],
            'cols' => [
                [
                    'id' => 3,
                    'order' => 0,
                    'name' => [
                        'fr' => 'Critère n°1'
                    ]
                ],
                [
                    'id' => 4,
                    'order' => 1,
                    'name' => [
                        'fr' => 'Critère n°2'
                    ]
                ]
            ],
            'type' => 'matrix_checkbox',
            'answer_min' => 0
        ],
        12 => [
            'id' => 12,
            'name' => [
                'fr' => ''
            ],
            'image' => null,
            'type' => 'net_promoter_score',
            'answer_min' => 1,
            'answers' => [
                [
                    'id' => 10,
                    'image' => null,
                    'order' => 1,
                    'value' => 1,
                    'name' => ['fr'=>'']
                ],
                [
                    'id' => 11,
                    'image' => null,
                    'order' => 2,
                    'value' => 1,
                    'name' => ['fr'=>'']
                ],
                [
                    'id' => 12,
                    'image' => null,
                    'order' => 3,
                    'value' => 1,
                    'name' => ['fr'=>'']
                ],
                [
                    'id' => 13,
                    'image' => null,
                    'order' => 4,
                    'value' => 1,
                    'name' => ['fr'=>'']
                ],
                [
                    'id' => 14,
                    'image' => null,
                    'order' => 5,
                    'value' => 1,
                    'name' => ['fr'=>'']
                ],
                [
                    'id' => 15,
                    'image' => null,
                    'order' => 6,
                    'value' => 1,
                    'name' => ['fr'=>'']
                ],
                [
                    'id' => 16,
                    'image' => null,
                    'order' => 7,
                    'value' => 1,
                    'name' => ['fr'=>'']
                ],
                [
                    'id' => 17,
                    'image' => null,
                    'order' => 8,
                    'value' => 1,
                    'name' => ['fr'=>'']
                ],
                [
                    'id' => 18,
                    'image' => null,
                    'order' => 9,
                    'value' => 1,
                    'name' => ['fr'=>'']
                ],
                [
                    'id' => 19,
                    'image' => null,
                    'order' => 10,
                    'value' => 1,
                    'name' => ['fr'=>'']
                ]
            ]
        ],
        13 => [
            'id' => 13,
            'name' => [
                'fr' => ''
            ],
            'image' => null,
            'type' => 'satisfaction',
            'answer_min' => 1,
            'answers' => [
                [
                    'id' => 20,
                    'image' => null,
                    'order' => 1,
                    'value' => 1,
                    'name' => ['fr'=>'']
                ],
                [
                    'id' => 21,
                    'image' => null,
                    'order' => 2,
                    'value' => 1,
                    'name' => ['fr'=>'']
                ],
                [
                    'id' => 22,
                    'image' => null,
                    'order' => 3,
                    'value' => 1,
                    'name' => ['fr'=>'']
                ],
                [
                    'id' => 23,
                    'image' => null,
                    'order' => 4,
                    'value' => 1,
                    'name' => ['fr'=>'']
                ],
                [
                    'id' => 24,
                    'image' => null,
                    'order' => 5,
                    'value' => 1,
                    'name' => ['fr'=>'']
                ],
                [
                    'id' => 25,
                    'image' => null,
                    'order' => 6,
                    'value' => 1,
                    'name' => ['fr'=>'']
                ],
                [
                    'id' => 26,
                    'image' => null,
                    'order' => 7,
                    'value' => 1,
                    'name' => ['fr'=>'']
                ],
                [
                    'id' => 27,
                    'image' => null,
                    'order' => 8,
                    'value' => 1,
                    'name' => ['fr'=>'']
                ],
                [
                    'id' => 28,
                    'image' => null,
                    'order' => 9,
                    'value' => 1,
                    'name' => ['fr'=>'']
                ],
                [
                    'id' => 29,
                    'image' => null,
                    'order' => 10,
                    'value' => 1,
                    'name' => ['fr'=>'']
                ]
            ]
        ],
        14 => [
            'id' => 14,
            'name' => [
                'fr' => ''
            ],
            'type' => 'sequence'
        ]
    ];

    public static function getAll()
    {
        return array_values(self::$mock_up);
    }

    public static function get($id = null)
    {
        if (isset(self::$mock_up[$id]))
            return self::$mock_up[$id];

        return false;
    }

    public static function getTypes()
    {
        return array_values(self::$types);
    }

    public static function typeExists($name = null)
    {
        foreach (self::$types as $type)
        {
            if ($type['name'] === $name)
                return $type;
        }

        return false;
    }
}
