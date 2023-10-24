<?php

namespace Musonza\DynamoBreeze;

use Aws\DynamoDb\Marshaler;

class ExpressionAttributeHandler
{
    public Marshaler $marshaler;

    public function __construct(Marshaler $marshaler)
    {
        $this->marshaler = $marshaler;
    }

    public function replacePlaceholders(array $expressionAttributes, array $dataProvider): array
    {
        foreach ($expressionAttributes as &$attribute) {
            foreach ($attribute as &$value) {
                foreach ($dataProvider as $placeholder => $replacement) {
                    $value = str_replace("<$placeholder>", $replacement, $value);
                }
            }
        }

        return $expressionAttributes;
    }

    public function marshalExpressionAttributeValues(array $expressionAttributes): array
    {
        foreach ($expressionAttributes as $key => $attribute) {
            foreach ($attribute as $type => $value) {
                // Ensure the value is in the correct format and not already marshaled
                if ($type === 'S' && is_string($value)) {
                    $expressionAttributes[$key] = [$type => $value];
                } elseif ($type === 'N' && is_numeric($value)) {
                    $expressionAttributes[$key] = [$type => (string) $value];
                } else {
                    $expressionAttributes[$key] = $this->marshaler->marshalValue($value);
                }
            }
        }

        return $expressionAttributes;
    }

    public function prepareExpressionAttributes(array $expressionAttributes, array $dataProvider): array
    {
        $replacedAttributes = $this->replacePlaceholders($expressionAttributes, $dataProvider);

        return $this->marshalExpressionAttributeValues($replacedAttributes);
    }
}
