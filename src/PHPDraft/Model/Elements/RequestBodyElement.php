<?php
/**
 * This file contains the RequestBodyElement
 *
 * @package PHPDraft\Model
 * @author  Sean Molenaar<sean@seanmolenaar.eu>
 */

namespace PHPDraft\Model\Elements;

use PHPDraft\Model\StructureElement;

/**
 * Class RequestBodyElement
 */
class RequestBodyElement extends ObjectStructureElement implements StructureElement
{

    /**
     * Parse a JSON object to a data structure
     *
     * @param \stdClass $object       An object to parse
     * @param array     $dependencies Dependencies of this object
     *
     * @return ObjectStructureElement self reference
     */
    public function parse($object, &$dependencies)
    {
        if (empty($object) || !isset($object->content))
        {
            return $this;
        }

        $this->element = $object->element;

        if (isset($object->content) && is_array($object->content))
        {
            foreach ($object->content as $value) {
                $struct        = new RequestBodyElement();
                $this->value[] = $struct->parse($value, $dependencies);
            }

            return $this;
        }

        $this->parse_common($object, $dependencies);

        if ($this->type === 'object')
        {
            $value       = isset($object->content->value->content) ? $object->content->value : NULL;
            $this->value = new RequestBodyElement();
            $this->value = $this->value->parse($value, $dependencies);

            return $this;
        }

        if ($this->type === 'array')
        {
            $this->value = new ArrayStructureElement();
            $this->value = $this->value->parse($object, $dependencies);

            return $this;
        }

        $this->value = isset($object->content->value->content) ? $object->content->value->content : NULL;

        return $this;
    }

    /**
     * Print the request body as a string
     *
     * @param string $type The type of request
     *
     * @return string Request body
     */
    public function print_request($type = 'application/x-www-form-urlencoded')
    {
        if (is_array($this->value))
        {
            $return = '<code class="request-body">';
            $list   = [];
            foreach ($this->value as $object) {
                if (get_class($object) === self::class)
                {
                    $list[] = $object->print_request($type);
                }
            }

            switch ($type) {
                case 'application/x-www-form-urlencoded':
                    $return .= join('&', $list);
                    break;
                default:
                    $return .= join(PHP_EOL, $list);
                    break;
            }

            $return .= '</code>';

            return $return;
        }

        $value = (empty($this->value)) ? '?' : $this->value;

        switch ($type) {
            case 'application/x-www-form-urlencoded':
                return $this->key . '=<span>' . $value . '</span>';
                break;
            default:
                $object             = [];
                $object[$this->key] = $value;

                return json_encode($object);
                break;
        }
    }

    /**
     *
     * @return string
     */
    function __toString()
    {
        return parent::__toString();
    }

}