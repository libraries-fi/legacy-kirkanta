<?php

namespace Kirkanta\Form;

use DoctrineORMModule\Form\Element\EntitySelect;
use Kirkanta\Entity\City;
use Kirkanta\Entity\Consortium;
use Kirkanta\Entity\Organisation;
use Kirkanta\Filter\ResetArrayIndices;
use Kirkanta\Filter\SortCustomData;
use Kirkanta\Form\Element\RoleSelect;
use Kirkanta\Util\OrganisationBranchTypes;
use Kirkanta\Util\OrganisationTypes;
use Kirkanta\Validator\Coordinates as CoordinatesValidator;

class OrganisationForm extends EntityForm
{
    protected $form_id = 'organisation-form';

    public function getTitle()
    {
        return $this->getObject()->isNew()
            ? $this->tr('New organisation')
            : $this->getObject()->getName();
    }

    public function init()
    {
        parent::init();

        $this->add([
            'name' => 'state',
            'type' => 'select',
            'options' => [
                'label' => $this->tr('State'),
                'value_options' => [
                    0 => $this->tr('Hidden'),
                    1 => $this->tr('Published'),
                ],
            ]
        ], ['priority' => 100]);

        /*
         * Basic details
         */

        $this->add([
            'name' => 'name',
            'options' => [
                'label' => $this->tr('Name'),
                'translatable' => true,
            ]
        ]);

        $this->add([
            'name' => 'short_name',
            'options' => [
                'label' => $this->tr('Short name'),
                'translatable' => true,
            ]
        ]);

        $this->add([
            'name' => 'type',
            'type' => 'select',
            'options' => [
                'label' => $this->tr('Type'),
                'empty_option' => '',
                'value_options' => (new OrganisationTypes($this->getTranslator()))->getTypes(),
            ]
        ]);

        $this->add([
            'name' => 'branch_type',
            'type' => 'select',
            'options' => [
                'label' => $this->tr('Branch type'),
                'empty_option' => '',
                'required' => false,
                'value_options' => (new OrganisationBranchTypes($this->getTranslator()))->getTypes(),
                'info' => $this->tr('Branch type is only available for organisations with the type of Library.')
            ]
        ]);

        $this->add([
            'name' => 'parent',
            'type' => EntitySelect::class,
            'options' => [
                'label' => $this->tr('Parent organisation'),
                'empty_option' => '',
                'target_class' => Organisation::class,
                'find_method'    => [
                    'name'   => 'findBy',
                    'params' => [
                        'criteria' => [
                            'group' => $this->getObject()->getGroup(),
                        ],
                        'orderBy'  => ['name' => 'asc']
                    ]
                ]
            ]
        ]);

        $this->add([
            'name' => 'city',
            'type' => EntitySelect::class,
            'options' => [
                'label' => $this->tr('City'),
                'empty_option' => '',
                'target_class' => City::class,
                'find_method'    => [
                    'name'   => 'findBy',
                    'params' => [
                        'criteria' => [],
                        'orderBy'  => ['name' => 'asc']
                    ]
                ]
            ]
        ]);

        $this->add([
            'name' => 'consortium',
            'type' => EntitySelect::class,
            'options' => [
                'label' => $this->tr('Consortium'),
                // 'empty_option' => $this->tr('-- Municipality default --'),
                // 'empty_value' => '0',
                'target_class' => Consortium::class,
                'find_method'    => [
                    'name'   => 'findBy',
                    'params' => [
                        'criteria' => ['special' => true],
                        'orderBy'  => ['name' => 'asc']
                    ]
                ]
            ]
        ]);

        /*
         * Array_merge() does not preserve keys so we use + operator to join them. The 'foo' element
         * in the second array is cancelled out by the 0-index in the first array.
         */
        $consortiums = [
            '0' => $this->tr('-- Municipality default --'),
            '-1' => $this->tr('-- No consortium --')
        ] + array_merge(['foo'], $this->get('consortium')->getValueOptions());

        $this->get('consortium')->setValueOptions($consortiums);

        $organisation = $this->getObject();

        if ($fallback = $organisation->getFallbackConsortium()) {
            if ($organisation->isFallbackConsortiumAllowed()) {
                $city = $this->getObject()->getCity();
                $label = sprintf($this->tr('The fallback value for %s is %s.'), $city->getName(), $fallback->getName());
                $this->get('consortium')->setOption('info', $label);
            }
        } else {
            $label = $this->tr('By default the consortium is defined by the selected city.');
            $this->get('consortium')->setOption('info', $label);
        }

        $this->add([
            'name' => 'slogan',
            'type' => 'text',
            'options' => [
                'label' => $this->tr('Slogan'),
                'translatable' => true,
                'info' => $this->tr('Exposed via API. Will be enabled in the Library Directory after an upgrade later on.'),
            ],
            'attributes' => [
                'maxlength' => 150
            ]
        ]);

        $this->add([
            'name' => 'description',
            'type' => 'textarea',
            'options' => [
                'label' => $this->tr('Description'),
                'translatable' => true,
                'info' => $this->tr('Exposed via API. Will be enabled in the Library Directory after an upgrade later on.')
            ],
            'attributes' => [
                'class' => 'richtext',
                'rows' => 20,
                'cols' => 40,
            ]
        ]);

        $this->add([
            'name' => 'legacy_description',
            'type' => 'textarea',
            'options' => [
                'label' => $this->tr('Description (old)'),
                'translatable' => true,
                'info' => $this->tr('This field will be removed after the Library Directory frontend is upgraded. It is replaced by Slogan and rich text description.'),
            ],
            'attributes' => [
                'rows' => 10,
                'cols' => 40,
            ]
        ]);

        $this->add([
            'name' => 'isil',
            'options' => [
                'label' => $this->tr('ISIL'),
            ]
        ]);

        $this->add([
            'name' => 'identificator',
            'options' => [
                'label' => $this->tr('Official ID'),
            ]
        ]);

        $this->add([
            'name' => 'founded',
            'options' => [
                'label' => $this->tr('Year founded'),
            ]
        ]);

        /*
         * Contact details
         */

        $this->add([
            'name' => 'homepage',
            'options' => [
                'label' => $this->tr('Homepage'),
                'translatable' => true,
            ]
        ]);

        $this->add([
            'name' => 'web_library',
            'options' => [
                'label' => $this->tr('Web Library URL'),
                'translatable' => true,
            ],
        ]);

        if ($consortium = $this->getObject()->getConsortium(true)) {
            $this->get('web_library')->setOption('info', $this->tr('Defaults to the address configured for the consortium.'));
            $this->get('web_library')->setAttribute('placeholder', $consortium->getHomepage());
        }

        $this->add([
            'name' => 'email',
            'options' => [
                'label' => $this->tr('Email address'),
                'translatable' => true,
            ]
        ]);

        $this->add([
            'name' => 'buses',
            'options' => [
                'label' => $this->tr('Buses'),
            ]
        ]);

        $this->add([
            'name' => 'trains',
            'options' => [
                'label' => $this->tr('Trains'),
            ]
        ]);

        $this->add([
            'name' => 'trams',
            'options' => [
                'label' => $this->tr('Trams'),
            ]
        ]);

        $this->add([
            'name' => 'transit_directions',
            'type' => 'textarea',
            'options' => [
                'label' => $this->tr('Traffic info'),
                'translatable' => true,
            ]
        ]);

        $this->add([
            'name' => 'parking_instructions',
            'type' => 'textarea',
            'options' => [
                'label' => $this->tr('Parking instructions'),
                'translatable' => true,
            ]
        ]);

        $this->add([
            'name' => 'coordinates',
            'options' => [
                'label' => $this->tr('Coordinates'),
            ]
        ]);

        $this->add([
            'name' => 'address',
            'type' => Fieldset\AddressFieldset::class,
            'options' => [
                'label' => $this->tr('Location'),
            ],
            'attributes' => [
                'class' => 'nested',
            ]
        ]);

        $this->add([
            'name' => 'mail_address',
            'type' => Fieldset\MailAddressFieldset::class,
            'options' => [
                'label' => $this->tr('Mail address'),
            ],
            'attributes' => [
                'class' => 'nested',
            ]
        ]);

        $this->add([
            'name' => 'phone_numbers',
            'type' => 'collection',
            'options' => [
                'label' => $this->tr('Phone numbers'),
                'count' => 1,
                'should_create_template' => true,
                'allow_add' => true,
                'allow_remove' => true,
                'target_element' => [
                    'type' => Fieldset\PhoneDetailsFieldset::class,
                ]
            ]
        ]);

        $this->add([
            'name' => 'construction_year',
            'type' => 'number',
            'options' => [
                'label' => $this->tr('Construction year'),
            ],
        ]);

        $this->add([
            'name' => 'building_name',
            'options' => [
                'label' => $this->tr('Building name'),
                'translatable' => true,
            ],
        ]);

        $this->add([
            'name' => 'building_architect',
            'options' => [
                'label' => $this->tr('Building architect'),
            ],
        ]);

        $this->add([
            'name' => 'interior_designer',
            'options' => [
                'label' => $this->tr('Interior designer'),
            ],
        ]);

        $this->add([
            'name' => 'custom_data',
            'type' => Element\CustomDataCollection::class,
            'options' => [
                'label' => $this->tr('Custom data'),
                'should_create_template' => true,
            ]
        ]);



        if (in_array('helmet', $this->getObject()->getGroup()->getTree())) {
            $this->add([
                'name' => 'helmet_sierra_id',
                'options' => [
                    'label' => $this->tr('Sierra ID'),
                    'info' => $this->tr('Identificator in Helmet reservation system.')
                ]
            ]);
        }

        $this->get('address')->setTranslator($this->getTranslator());
        $this->get('mail_address')->setTranslator($this->getTranslator());

        $this->getEventManager()->attach('init', function($event) {
            if (!$this->getObject()->isFallbackConsortiumAllowed()) {
                $this->get('consortium')->setValue(-1);
            }
        });

        if ($this->isAllowed('admin')) {
            $this->add([
                'name' => 'group',
                'type' => RoleSelect::class,
                'options' => [
                    'label' => $this->tr('Owner'),
                ]
            ], ['priority' => 10]);

            $this->add([
                'name' => 'slug',
                'options' => [
                    'label' => $this->tr('Slug'),
                    'translatable' => true,
                    'info' => $this->tr('Replaces the numeric ID of the organisation on the public website.')
                ]
            ]);
        }
    }

    public function getInputFilterSpecification()
    {
        return [
            [
                'name' => 'name',
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 3,
                            'max' => 200,
                        ]
                    ]
                ]
            ],
            [
                'name' => 'group',
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'ToInt'],
                ],
            ],
            [
                'name' => 'short_name',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 3,
                            'max' => 200,
                        ]
                    ]
                ]
            ],
            [
                'name' => 'slug',
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StringToLower'],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 3,
                            'max' => 60,
                        ]
                    ],
                    [
                        'name' => 'Regex',
                        'options' => [
                            'pattern' => '/^[a-z][a-z0-9-]+$/'
                        ]
                    ],
                    [
                        'name' => 'UniqueValue',
                        'options' => [
                            'entity_id' => $this->getObject()->getId(),
                            'entity_class' => get_class($this->getObject()),
                            'field' => 'slug',
                        ]
                    ]
                ]
            ],
            [
                'name' => 'state',
                'required' => false,
                'filters' => [
                    ['name' => 'ToInt']
                ],
                'validators' => [
                    [
                        'name' => 'Between',
                        'options' => [
                            'min' => -1,
                            'max' => 1,
                        ]
                    ]
                ]
            ],
            [
                'name' => 'email',
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name' => 'EmailAddress',
                    ]
                ],
            ],
            [
                'name' => 'parent',
                'required' => false,
                'filters' => [
                    ['name' => 'ToInt'],
                    ['name' => 'ToNull', 'options' => ['type' => 'integer']],
                ],
                'validators' => [
                    [
                        'name' => 'EntityNoRecursion',
                        'options' => [
                            'entity_id' => $this->getObject()->getId(),
                            'entity_class' => get_class($this->getObject()),
                            'getter' => 'getParent',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'city',
                'required' => true,
                'filters' => [
                    ['name' => 'ToInt'],
                    ['name' => 'ToNull', 'options' => ['type' => 'integer']],
                ],
            ],
            [
                'name' => 'consortium',
                'required' => false,
                'filters' => [
                    ['name' => 'ToInt'],
                    [
                        'name' => 'Callback',
                        'options' => [
                            'callback' => function($value) {
                                if ($value === -1) {
                                    $this->getObject()->setFallbackConsortiumAllowed(false);
                                    return null;
                                }
                                $this->getObject()->setFallbackConsortiumAllowed(true);
                                return $value;
                            }
                        ]
                    ],
                    ['name' => 'ToNull'],
                ],
            ],
            [
                'name' => 'branch_type',
                'required' => false,
                'filters' => [
                    ['name' => 'ToNull', 'options' => ['type' => 'string']],
                ]
            ],
            [
                'name' => 'construction_year',
                'required' => false,
                'filters' => [
                    ['name' => 'ToInt'],
                    ['name' => 'ToNull', 'options' => ['type' => 'integer']],
                ],
                'validators' => [
                    [
                        'name' => 'Between',
                        'options' => [
                            'min' => 0,
                            'max' => 9999,
                        ]
                    ]
                ]
            ],
            [
                'name' => 'translations',
                'required' => false,
            ],
            [
                'name' => 'homepage',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'ToNull', 'options' => ['type' => 'string']],
                    [
                        'name' => 'UriNormalize',
                        'options' => [
                            'default_scheme' => 'http',
                        ],
                    ],
                ],
                'validators' => [
                    [
                        'name' => 'Uri',
                        'options' => [
                            'allowRelative' => false,
                        ],
                    ],
                ],
            ],
            [
                'name' => 'web_library',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'ToNull', 'options' => ['type' => 'string']],
                    [
                        'name' => 'UriNormalize',
                        'options' => [
                            'default_scheme' => 'http',
                        ],
                    ],
                ],
                'validators' => [
                    [
                        'name' => 'Uri',
                        'options' => [
                            'allowRelative' => false,
                        ],
                    ],
                ],
            ],
            [
                'name' => 'isil',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'ToNull', 'options' => ['type' => 'string']],
                ]
            ],
            [
                'name' => 'identificator',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'ToNull', 'options' => ['type' => 'string']],
                ]
            ],
            [
                'name' => 'building_name',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'ToNull', 'options' => ['type' => 'string']],
                ]
            ],
            [
                'name' => 'building_architect',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'ToNull', 'options' => ['type' => 'string']],
                ]
            ],
            [
                'name' => 'interior_designer',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'ToNull', 'options' => ['type' => 'string']],
                ]
            ],
            [
                'name' => 'founded',
                'required' => false,
                'filters' => [
                    ['name' => 'ToInt'],
                    ['name' => 'ToNull', 'options' => ['type' => 'integer']],
                ],
                'validators' => [
                    [
                        'name' => 'Between',
                        'options' => [
                            'min' => 1800,
                            'max' => 9999,
                        ]
                    ]
                ]
            ],
            [
                'name' => 'coordinates',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'ToNull', 'options' => ['type' => 'string']],
                ],
                'validators' => [
                    ['name' => CoordinatesValidator::class],
                ]
            ],
            [
                'name' => 'legacy_description',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                ]
            ],
            [
                'name' => 'transit_directions',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                ]
            ],
            [
                'name' => 'parking_instructions',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                ]
            ],
            [
                'name' => 'buses',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'max' => 200
                        ]
                    ]
                ]
            ],
            [
                'name' => 'trams',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'max' => 200
                        ]
                    ]
                ]
            ],
            [
                'name' => 'trains',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'max' => 200
                        ]
                    ]
                ]
            ],
            [
                'name' => 'helmet_sierra_id',
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                ]
            ],
            [
                'name' => 'custom_data',
                'required' => false,
                'filters' => [
                    ['name' => ResetArrayIndices::class],
                    ['name' => SortCustomData::class],
                ]
            ],
        ];
    }
}
