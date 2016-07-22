<?php

/**
 * Класс для создания объекта одной записи Irbis (книги)
 */
class IrbisItem {
	/**
	 * «Голые» данные
	 * @var string
	 */
	public $raw = '';

	/**
	 * Все поля в виде ассоциативного массива
	 * @var array
	 */
	public $fields = array();



	/**
	 * Инициализация
	 * @param string $raw «голые» данные
	 */
	public function __construct($raw) {

		$this->raw = trim($raw);

		foreach(explode("#", $this->raw) as $raw_line) {

			$raw_line = trim($raw_line);

			if(!empty($raw_line)) {
				list($key, $value) = explode(': ', $raw_line, 2);

				$value = trim($value);

				// Если поле с этим номером уже есть, то это поле будет массивом
				if(isset( $this->fields[$key] )) {

					// Если это ещё не массив, то создадим его и добавим текущее значение как первый элемент массива
					if(! is_array($this->fields[$key])) {

						$this->fields[$key] = Array($this->fields[$key]);
					}

					// Добавим нужное значение в массив
					array_push($this->fields[$key], $value);

				} else { // Если такое поле встречается впервые — просто создадим его
					$this->fields[$key] = $value;
				}
			}
		}

		return $this;
	}

	/**
	 * Получить значение поля или подполя
	 * @param  integer $fieldID  ID поля (см. документацию Irbis)
	 * @param  string  $subfield Ключ подполя. Буква обозначающая пределённое подполе
	 * @return string            Значение поля или подполя
	 */
	public function field($fieldID, $subfield = null) {
		$field = new IrbisField($this->fields[$fieldID]);

		if(isset($subfield)) {
			return $field->sub($subfield);

		} else {
			return $field;
		}
	}

	/**
	 * Проверить существование поля
	 * @param  integer $fieldID  ID поля (см. документацию Irbis)
	 * @return boolean           Существует ли объект
	 */
	public function has($fieldID, $subfield = null) {

		if (! empty( $this->fields[$fieldID] )) {

			if(isset($subfield)) {
				$field = new IrbisField($this->fields[$fieldID]);
				$subfieldValue = $field->sub($subfield);

				return (!empty( $subfieldValue ));
			} else return true;

		} else return false;
	}

}
