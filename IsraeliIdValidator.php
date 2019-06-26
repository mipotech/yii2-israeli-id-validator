<?php

namespace mipotech\yii2israelidvalidator;

use Yii;
use yii\validators\Validator;

/**
 *
 * @author Chaim Leichman, MIPO Technologies LTD
 *
 * Inspired by:
 * @link http://opencodeoasis.blogspot.com/2008/08/blog-post_10.html
 */
class IsraeliIdValidator extends Validator
{
    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        $errorMessages = $this->errorMessages;

        if (strlen($value) < 5) {
            $this->addError($model, $attribute, $errorMessages['tooShort']);
        } elseif (strlen($value) > 9) {
            $this->addError($model, $attribute, $errorMessages['tooLong']);
        } elseif (preg_match('/[^0-9]/', $value)) {
            $this->addError($model, $attribute, $errorMessages['invalidChars']);
        }

        $paddedValue = str_pad($value, 9, '0', STR_PAD_LEFT);

        $mone = 0;
        for ($i = 0; $i < 9; $i++) {
            $char = mb_substr($paddedValue, $i, 1);
            $incNum = intval($char);
            $incNum*=($i%2)+1;
            if ($incNum > 9) {
                $incNum -= 9;
            }
            $mone += $incNum;
        }

        if ($mone % 10 != 0) {
            $this->addError($model, $attribute, $errorMessages[]);
        }
    }

    /**
     * @inheritdoc
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        $errorMessages = $this->errorMessages;
        $errorMessages = array_map('addslashes', $errorMessages);

        $js = <<<EOF
if (value.length < 5) {
    messages.push('{$errorMessages['tooShort']}');
} else if (value.length > 9) {
    messages.push('{$errorMessages['tooLong']}');
} else if (/[^0-9]/.test(value)) {
    messages.push('{$errorMessages['invalidChars']}');
}

var paddedValue = value;
if (paddedValue.length < 9) {
    while(paddedValue.length < 9) {
         paddedValue = '0' + paddedValue;
    }
}

var mone = 0, incNum;
for (var i=0; i < 9; i++) {
    incNum = Number(paddedValue.charAt(i));
    incNum *= (i%2)+1;
    if (incNum > 9) {
         incNum -= 9;
    }
    mone += incNum;
}

if (mone % 10 != 0) {
    messages.push('{$errorMessages['invalidChecksum']}');
}
EOF;
        return $js;
    }

    /**
     * Generate an array of error messages
     *
     * @return array
     */
    protected function getErrorMessages(): array
    {
        return [
            'invalidChecksum' => Yii::t('tzvalidator', 'ID number is not valid'),
            'invalidChars' => Yii::t('tzvalidator', 'ID number must include only digits'),
            'tooLong' => Yii::t('tzvalidator', 'ID number must be no more than 9 digits'),
            'tooShort' => Yii::t('tzvalidator', 'ID number must be at least 5 digits'),
        ];
    }
}
