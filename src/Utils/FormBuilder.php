<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Utils;

# TODO: Finish up for builder
use Hyper\Exception\HyperException;
use Hyper\Reflection\Annotation;

/**
 * Class FormBuilder
 * @package Hyper\Utils
 */
class FormBuilder
{
    protected $form = '';
    /**
     * @var array|object
     */
    private $model;
    /**
     * @var string
     */
    private $method;
    /**
     * @var string
     */
    private $action;
    /**
     * @var string
     */
    private $enctype;
    /**
     * @var array
     */
    private $attributes;
    /**
     * @var array
     */
    private $skip;

    /**
     * FormBuilder constructor.
     * @param array|object $model
     * Models
     *  => new Class()
     *  => stdObject
     *  => [ 'hidden' => 'id', 'textarea' => 'description', 'select' => ['option1', 'option2'] ]
     * @param string $method
     * @param string $action
     * @param string $enctype
     * @param array $attributes
     * @param array $skip
     * @throws HyperException
     */
    public function __construct($model = [], $method = 'GET', $action = '', $enctype = '', $attributes = [], $skip = [])
    {
        $this->model = $model;
        $this->method = $method;
        $this->action = $action;
        $this->enctype = $enctype;
        $this->attributes = $attributes;
        $this->skip = $skip;

        if (is_array($model))
            $this->form = $this->array($model);
        elseif (class_exists(get_class($model)))
            $this->form = $this->model($model);
        elseif (is_object($model))
            $this->form = $this->object($model);
        else throw new HyperException('Cannot create form from this!');

        $this->skip = $skip;
    }

    /**
     * @param array $base
     * @return string
     */
    protected function array(array $base): string
    {
        return 'Array';

    }

    /**
     * @param object $object
     * @return string
     */
    protected function model(object $object): string
    {
        $class = get_class($object);
        $innerHtml = '';

        foreach ((array)$object as $property => $value) {
            if (array_search($property, $this->skip) === false) {
                if (strtolower($property) === 'id')
                    $innerHtml .= Html::hidden($property, $value);
                else {
                    $field = Html::text($property, $value, [
                        'placeholder' => ucfirst($property),
                        'class' => is_bool($value) ? 'checkbox-inline' : 'form-control',
                        'type' => Annotation::getPropertyAnnotation($class, $property,
                            'file') ? 'file' : (is_bool($value) ? 'checkbox' : (is_numeric($value) ? 'number' : 'text'))
                    ]);
                    $label = Html::label(strtoupper($property), $property,
                        ['class' => 'text-capitalize text-muted']);
                    $innerHtml .= Html::div(
                        $label . $field, ['class="row m-2 m"']
                    );
                }
            }
        }

        $innerHtml .= Html::div(''
            . Html::submit($class, 'Submit', ['class' => 'btn btn-primary'])
            . Html::reset($class, 'Reset', ['class' => 'btn btn-default']),
            ['class="form-group row ma-2 m-2 m"']
        );

        return Html::form(
            $this->action,
            $this->method,
            $innerHtml,
            array_merge(['enctype' => $this->enctype], $this->attributes)
        );
    }

    /**
     * @param object $object
     * @return string
     */
    protected function object(object $object): string
    {
        return Html::form('', 'GET');
    }

    #region Forms

    /**
     * @param $model
     * @param array $attributes
     * @param array $skip
     * @return string
     * @throws HyperException
     */
    public static function create($model, $attributes = [], $skip = [])
    {
        return (new FormBuilder(
            $model,
            @$attributes['method'] ?? 'GET',
            @$attributes['action'] ?? '',
            $attributes['enctype'],
            $attributes,
            $skip))->getForm();
    }

    /**
     * @return string
     */
    public function getForm(): string
    {
        return $this->form;
    }

    /**
     *
     */
    public function getFields()
    {

    }
    #endrgion
}