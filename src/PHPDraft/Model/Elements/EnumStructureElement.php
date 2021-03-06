<?php
/**
 * This file contains the ${FILE_NAME}
 *
 * @package php-drafter\SOMETHING
 * @author  Sean Molenaar<sean@seanmolenaar.eu>
 */

namespace PHPDraft\Model\Elements;

use Michelf\MarkdownExtra;
use PHPDraft\Model\StructureElement;

class EnumStructureElement implements StructureElement
{
    /**
     * Object description
     *
     * @var string
     */
    public $description;
    /**
     * Type of element
     *
     * @var string
     */
    public $element = NULL;
    /**
     * Object value
     *
     * @var mixed
     */
    public $value = NULL;
    /**
     * /**
     * List of object dependencies
     *
     * @var string[]
     */
    public $deps;

    /**
     * Parse a JSON object to a structure
     *
     * @param \stdClass $object       An object to parse
     * @param array     $dependencies Dependencies of this object
     *
     * @return EnumStructureElement self reference
     */
    function parse($object, &$dependencies)
    {
        $this->element     = (isset($object->element)) ? $object->element : NULL;
        $this->description = (isset($object->meta->description)) ? htmlentities($object->meta->description) : NULL;
        if (isset($object->content) && is_array($object->content))
        {
            $deps = [];
            foreach ($object->content as $value) {
                $element       = new EnumStructureElement();
                $this->value[] = $element->parse($value, $deps);
            }

            $this->element = $this->element . '(' . $deps[0] . ')';
        } else {
            $this->value = (isset($object->content)) ? $object->content : NULL;
        }

        $this->description_as_html();

        $dependencies[] = $this->element;

        return $this;
    }

    /**
     * Parse the description to HTML
     *
     * @return void
     */
    public function description_as_html()
    {
        $this->description = MarkdownExtra::defaultTransform($this->description);
    }

    /**
     * Print a string representation
     *
     * @return string
     */
    function __toString()
    {
        if (is_array($this->value))
        {
            $return = '<ul class="list-group">';
            foreach ($this->value as $item) {
                $return .= '<li class="list-group-item">' . $item->simple_string() . '</li>';
            }

            $return .= '</ul>';

            return $return;
        }

        $type   = (!in_array($this->element, self::DEFAULTS)) ?
            '<a class="code" href="#object-' . str_replace(' ', '-',
                strtolower($this->element)) . '">' . $this->element . '</a>' : '<code>' . $this->element . '</code>';
        $return = '<tr>' .
            '<td><span>' . $this->value . '</span></td>' .
            '<td>' . $type . '</td>' .
            '<td>' . $this->description . '</td>' .
            '</tr>';

        return $return;
    }

    function simple_string()
    {
        $return = '<span>' . $this->value . "</span>";

        if (!empty($this->description)) {
            $return .= ' - ' . $this->description;
        }


        return $return;
    }

    function strval()
    {
        if (is_array($this->value)) {
            $key = rand(0, count($this->value));
            if (is_subclass_of($this->value[$key], StructureElement::class)) {
                return $this->value[$key]->strval();
            }

            return $this->value[$key];
        }

        return $this->value;
    }
}