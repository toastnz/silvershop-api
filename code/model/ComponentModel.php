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

    public function forTemplate()
    {
        $componentTitle = preg_replace('/([a-z])([A-Z])/s', '$1 $2', $this->type);

        $data = [
            'Title' => _t('TOASTSHOP' . $this->type .'CheckoutComponentTitle', $componentTitle),
            'Fields' => $this->component->getFormFields($this->order)
        ];

        $viewer = new ViewableData();
        return $viewer->customise($data)->renderWith([$this->type . 'CheckoutComponent', 'CheckoutComponent'])->forTemplate();
    }
}
