<?php

namespace Kirkanta\Form;

use BjyAuthorize\Service\Authorize;
use BjyAuthorize\Provider\Identity\ProviderInterface;
use Kirkanta\EntityPluginManager;
use Zend\Form\Element\Select;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\InputFilter\InputFilterProviderInterface;

abstract class AbstractSearchForm extends Form implements InputFilterProviderInterface
{
    protected $actions = Form::SUBMIT_BUTTON;
    private $auth;

    public static function createInstance(EntityPluginManager $plugins, array $options)
    {
        $sm = $plugins->getServiceLocator();
        return new static(
            $sm->get(Authorize::class),
            $sm->get('MvcTranslator'),
            $options
        );
    }

    public function __construct(Authorize $auth, TranslatorInterface $translator, array $options)
    {
        $this->form_id = get_class($this);
        parent::__construct($translator, $options);
        $this->auth = $auth;
        $this->user = $options['user'];
    }

    public function init()
    {
        $this->setAttribute('class', 'search-form');
        $this->setAttribute('method', 'get');
    }

    public function isAllowed($action)
    {
        return $this->auth->isAllowed('entity', $action);
    }

    public function prepare()
    {
        $this->injectActionButtons();

        if ($submit = $this->get('submit')) {
            $submit->setOption('button_role', 'default');
            $submit->setLabel($this->tr('Search'));
        }

        foreach ($this->elements as $element) {
            if ($element instanceof Select) {
                if (!$element->getEmptyOption()) {
                    $element->setEmptyOption($this->tr('All'));
                }
            }
        }

        parent::prepare();
    }

    public function getActions()
    {
        return $this->getStandardActions($this->actions);
    }

    protected function injectActionButtons()
    {
        array_map([$this, 'add'], $this->getActions());
    }

    public function getCurrentUser()
    {
        return $this->user;
    }
}
