<?php

namespace Kirkanta\Form;

use BjyAuthorize\Service\Authorize;
use DoctrineModule\Persistence\ProvidesObjectManager;
use Zend\EventManager\EventManager;
use Zend\Form\Element\Collection as CollectionElement;
use Zend\Form\ElementInterface;
use Zend\Form\FieldsetInterface;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Hydrator\HydratorInterface;

use Kirkanta\Filter\EmptyTranslations;
use Kirkanta\Hydrator\ProperDoctrineObject as DoctrineHydrator;
use Kirkanta\EntityPluginManager;
use Kirkanta\I18n\Form\TranslatableFormInterface;
use Kirkanta\I18n\Form\TranslatableFormTrait;
use Kirkanta\I18n\InputFilter\TranslationsInputFilter;
use Kirkanta\I18n\TranslatableInterface;
use Kirkanta\I18n\TranslatableTrait;
use Kirkanta\I18n\Translations;

abstract class EntityForm extends Form implements InputFilterProviderInterface, TranslatableFormInterface
{
    use ProvidesObjectManager;
    use TranslatableFormTrait;

    private $auth;
    protected $form_id = '';
    private $plugins;
    protected $url_builder;

    private $events;

    protected $buttons = [
        'submit' => null,
        'cancel' => null,
        'delete' => null,
    ];

    public static function createInstance(EntityPluginManager $plugins, array $options)
    {
        $sm = $plugins->getServiceLocator();
        $translator = $sm->get('MvcTranslator');
        $hydrator = new DoctrineHydrator($sm->get('Doctrine\ORM\EntityManager'));
        $auth = $sm->get(Authorize::class);
        return new static($plugins, $hydrator, $translator, $auth, $options);
    }

    public function __construct(EntityPluginManager $plugins, HydratorInterface $hydrator, TranslatorInterface $translator, Authorize $auth, array $options)
    {
        parent::__construct($translator, $options);

        $this->setAttribute('class', 'document-form');
        $this->user = $options['user'];

        $this->auth = $auth;
        $this->plugins = $plugins;
        $this->url_builder = $this->plugins->urlBuilder($options['entity_class']);
        $this->actions = self::SUBMIT_BUTTON | self::CANCEL_BUTTON;

        if (!$this->getObject()->isNew()) {
            $this->actions |= self::DELETE_BUTTON;
        }
    }

    public function getEventManager()
    {
        if (!$this->events) {
            $this->events = new EventManager;
            $this->events->setIdentifiers([__CLASS__, get_class($this)]);
        }
        return $this->events;
    }

    public function getObject()
    {
        return parent::getObject() ?: $this->getOption('entity');
    }

    public function getPluginManager()
    {
        return $this->plugins;
    }

    public function getButton($name)
    {
        if (!$this->buttons[$name]) {
            $actions = $this->getStandardActions(-1);
            $this->buttons[$name] = $actions[$name];
        }
        return $this->buttons[$name];
    }

    public function getTitle()
    {
        return $this->getObject()->isNew()
            ? $this->tr('New resource')
            : sprintf($this->tr('Edit #%d'), $this->getObject()->getId());
    }

    public function init()
    {
        if ($this->isTranslatable()) {
            $this->injectTranslationsElement();
            $this->injectLanguageSelector();
        }
    }

    public function isAllowed($action)
    {
        return $this->auth->isAllowed('entity', $action);
    }

    protected function isTranslatable()
    {
        return $this->getObject() instanceof TranslatableInterface;
    }

    public function getActions()
    {
        return $this->getStandardActions($this->actions);
    }

    public function getLocales()
    {
        return $this->getLanguageProvider()->getLocales();
    }

    public function injectActionButtons()
    {
        array_map([$this, 'add'], $this->getActions());

        if ($this->has('submit')) {
            $this->get('submit')->setLabel($this->tr('Save'));
        }
    }

    protected function injectHydrator($hydrator, FieldsetInterface $fieldset)
    {
        $fieldset->setHydrator($hydrator);

        foreach ($fieldset->getFieldsets() as $child) {
            if ($child->getObject()) {
                $this->injectHydrator($hydrator, $child);
            } elseif ($child instanceof CollectionElement) {
                $proto = $child->getTargetElement();

                if ($proto->getObject()) {
                    $this->injectHydrator($hydrator, $proto);
                }
            }
        }
    }

    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new TranslationsInputFilter;
        }
        $input_filter = parent::getInputFilter();

        if ($this->has('language') && $input_filter->get('translations')->getFilterChain()->count() == 0) {
            $filter = new EmptyTranslations;
            $filter->setOptions([
                'locales' => $this->getLocales(),
            ]);
            $input_filter->get('translations')->getFilterChain()->attach($filter);
        }

        return $input_filter;
    }

    public function setData($data)
    {
        parent::setData($data);

        if (isset($this->data['translations'])) {
            $this->data['translations'] = Translations::merge($this->data['translations'], $this->getObject()->getTranslations());
        }

        return $this;
    }

    public function getCurrentUser()
    {
        return $this->user;
    }
}
