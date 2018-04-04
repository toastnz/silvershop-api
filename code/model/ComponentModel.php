<?php

/**
 * Class ComponentModel
 */
class ComponentModel extends ShopModelBase
{
    /** @var CheckoutComponent $component */
    protected $component;

    protected $type;
    protected $form_fields;
    protected $html;

    protected static $fields = [
        'type',
        'html'
    ];

    public function __construct($type)
    {
        parent::__construct();

        $class = $type . 'CheckoutComponent';
        // Check if the type is valid
        if (class_exists($class)) {
            $this->type = $type;
            $this->component = new $class();

            // Get html
            $this->html = $this->forTemplate();

            $this->form_fields = $this->component->getFormFields($this->order);
        }
    }

    public function getComponentFields()
    {
        $fields = $this->form_fields;

        $this->extend('updateComponentFields', $fields);

        return $this->form_fields;
    }

    public function forTemplate()
    {
        $componentTitle = preg_replace('/([a-z])([A-Z])/s', '$1 $2', $this->type);

        $this->extend('updateComponentTitle', $componentTitle);

        $fields = $this->getComponentFields();

        $data = [
            'Title'  => _t('TOASTSHOP' . $this->type . 'CheckoutComponentTitle', $componentTitle),
            'Fields' => $fields
        ];

        return $this->order->customise($data)->renderWith([$this->type . 'CheckoutComponent', 'CheckoutComponent'])->forTemplate();
    }
}
