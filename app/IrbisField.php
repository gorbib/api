<?php

/**
 * Объект поля Irbis
 */
class IrbisField {
	private $value;

	public function __construct($value) {
		$this->value = $value;
	}

	public function __toString() {

		if(is_null($this->value))  return '';

		if (is_array($this->value)) return implode(', ', $this->value);

		return $this->value;
	}

	/**
	 * Получить значение подполя
	 * @param  string $subfieldKey Ключ подполя
	 * @return string              Значение подполя
	 */
	public function sub($subfieldKey) {

		if(is_array($this->value)) return $this->arraySubfields($this->value, $subfieldKey);

		$subfieldKey = strtoupper($subfieldKey);

		preg_match("/\^$subfieldKey([^^]*)[\^$]?/", $this->value, $subfieldValue);

		return $subfieldValue[1];
	}

	private function arraySubfields(array $fieldsArray, $subfieldKey) {
		$result = array();

		foreach ($fieldsArray as $field) {
			$field = new IrbisField($field);
			array_push($result, $field->sub($subfieldKey));
		}
		return $result;
	}
}
