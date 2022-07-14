<?php

namespace Khlystou\AnekdotRuApi;

class Anekdot
{
	private const BASE_URL_PATH = 'https://www.anekdot.ru';
	private const DEFAULT_OUTPUT_TYPE = 'array'; // array, json
	
	/**
	 * Анекдоты без политики.
	 *
	 * Возвращает анекдоты без политики за определённую дату.
	 * Дата в формате 01 день, 07 месяц, 2019 год.
	 * Можно передать колличество возвращаемых анекдотов, от 1 до 15.
	 * По умолчанию возвращает 15 анекдотов за текущий день.
	 *
	 * @param  integer	   $day    День
	 * @param  integer	   $month  Месяц
	 * @param  integer	   $year   Год
	 * @param  integer	   $count  Колличество анекдотов.
	 * @param  string|null $output Формат ответа
	 *
	 * @return array|string
	 */
	public static function getNoPolitical($day = null, $month = null, $year = null, $count = 15, $output = self::DEFAULT_OUTPUT_TYPE)
	{
		$PATH = '/last/non_burning/';

		if ($count > 15 || $count <= 0)
		{
			self::response(self::message('$count должен быть от 1 до 15.', 2), '');
		}

		$day = $day == null ? date("d") : $day;
		$month = $month == null ? date("m") : $month;
		$year = $year == null ? date("Y") : $year;

		$DATE = $year . '-' . $month . '-' . $day;

		$html = self::request(self::BASE_URL_PATH . $PATH . $DATE);

		preg_match_all('/<div class="topicbox"(.+?)<\/div><\/div><\/div>/mi', (string) $html, $response);

		$data = [];

		foreach ($response[0] as $k => $post)
		{

			preg_match_all('/<div class="text">(.+?)<\/div>/mi', $post, $text);
			preg_match_all('/<div class="num">(.+?)<\/div>/mi', $post, $rating);
			preg_match_all('/<div class="tags">(.+?)<\/div>/mi', $post, $tags);
			preg_match_all('/<\/span>(.+?)<\/a><\/div>/mi', $post, $author);
			preg_match_all('/<a href="\/id\/(.+?)\//mi', $post, $id);

			$tags = strip_tags($tags[1][0]);

			if (stripos($tags, ',') !== false)
			{
				$tags = explode(',', $tags);
				$tags = array_map('trim', $tags);
				$tags = array_map('mb_strtolower', $tags);
			}
			else
			{
				$tags = [mb_strtolower(trim($tags)) ];
			}

			$date = "$day.$month.$year";

			$text = $text[1][0];

			$text = preg_replace('/\s+/', ' ', $text);
			$text = str_ireplace('&quot;', '"', $text);
			$text = str_replace('<br>', "\n", $text);

			$data[$k]['id'] = strip_tags($id[1][0]);
			$data[$k]['date']['full'] = $date;
			$data[$k]['date']['day'] = $day;
			$data[$k]['date']['month'] = $month;
			$data[$k]['date']['year'] = $year;
			$data[$k]['text'] = $text;
			$data[$k]['rating'] = strip_tags($rating[1][0]);
			$data[$k]['tags'] = $tags;
			$data[$k]['author'] = strip_tags($author[1][0]);
		}

		$data = array_shift(array_chunk($data, $count));

		return self::response($data, strtolower($output));
	}

	/**
	 * Случайные анекдоты.
	 *
	 * Возвращает случайные анекдоты на разные темы.
	 * Можно передать колличество возвращаемых анекдотов, от 1 до 21.
	 * По умолчанию возвращает 21 анекдот.
	 *
	 *		Array
	 *		(
	 *		    [0] => Array
	 *		        (
	 *					[id] => 732965
	 *		            [date] => Array
	 *		                (
	 *		                    [full] => 18.11.2018
	 *		                    [day] => 18
	 * 		                    [year] => 2018
	 *		                )
	 *		            [text] => В женщине должен быть Я, а не какая-то там загадка.
	 *		            [rating] => 47
	 *		            [tags] => Array
	 *		                (
	 *		                    [0] => девушки
	 *		                    [1] => пошлые
	 *		                )
	 *
	 *		            [author] => Леонид Хлыновский
	 *		        )
	 *		)
	 *
	 * @param  integer	   $count  Колличество анекдотов.
	 * @param  string|null $output Формат ответа
	 *
	 * @return array|string
	 */
	public static function getRandom($count = 21, $output = self::DEFAULT_OUTPUT_TYPE)
	{
		$PATH = '/random/anekdot/';

		if ($count > 21 || $count <= 0)
		{
			self::response(self::message('$count должен быть от 1 до 21.', 2), '');
		}

		$html = self::request(self::BASE_URL_PATH . $PATH);

		preg_match_all('/<div class="topicbox"(.+?)<\/div><\/div><\/div>/mi', (string) $html, $response);

		$data = [];

		foreach ($response[0] as $k => $post)
		{

			preg_match_all('/<p class="title">(.+?)<\/a>/mi', $post, $date);
			preg_match_all('/<div class="text">(.+?)<\/div>/mi', $post, $text);
			preg_match_all('/<div class="num">(.+?)<\/div>/mi', $post, $rating);
			preg_match_all('/<div class="tags">(.+?)<\/div>/mi', $post, $tags);
			preg_match_all('/<\/span>(.+?)<\/a><\/div>/mi', $post, $author);
			preg_match_all('/<a href="\/id\/(.+?)\//mi', $post, $id);

			$tags = strip_tags($tags[1][0]);

			if (stripos($tags, ',') !== false)
			{
				$tags = explode(',', $tags);
				$tags = array_map('trim', $tags);
				$tags = array_map('mb_strtolower', $tags);
			}
			else
			{
				$tags = [mb_strtolower(trim($tags)) ];
			}

			$date = strip_tags($date[1][0]);
			list($day, $month, $year) = explode('.', $date);

			$text = $text[1][0];

			$text = preg_replace('/\s+/', ' ', $text);
			$text = str_ireplace('&quot;', '"', $text);
			$text = str_replace('<br>', "\n", $text);

			$data[$k]['id'] = strip_tags($id[1][0]);
			$data[$k]['date']['full'] = $date;
			$data[$k]['date']['day'] = $day;
			$data[$k]['date']['month'] = $month;
			$data[$k]['date']['year'] = $year;
			$data[$k]['text'] = $text;
			$data[$k]['rating'] = strip_tags($rating[1][0]);
			$data[$k]['tags'] = $tags;
			$data[$k]['author'] = strip_tags($author[1][0]);
		}

		$data = array_shift(array_chunk($data, $count));

		return self::response($data, strtolower($output));
	}

	/**
	 * Фильтрация анекдотов.
	 *
	 * Фильтрует по тегам, словам в тексте.
	 * Анекдот не подходящий под критерии удаляется из массива.
	 *
	 * @param  string	   $type		Тип фильтрации
	 * @param  array	   $data		Массив с анекдотами, полученный одним из методов класса
	 * @param  array       $blacklist   Чёрный список слов
	 * @param  string|null $output		Формат ответа
	 *
	 * @return array|string
	 */
	public static function filter($type = 'tags', $data = [], $blacklist = [], $output = self::DEFAULT_OUTPUT_TYPE)
	{
		if (count($data) == 0)
		{
			self::response(self::message('Аргумент $data пустой.', 2), '');
		}

		if (count($blacklist) == 0)
		{
			self::response(self::message('Аргумент $blacklist пустой.', 2), '');
		}

		if (mb_strtolower($type) == 'tags')
		{
			foreach ($data as $k => $d)
			{
				foreach ($blacklist as $item)
				{
					if ($data[$k] == null)
					{
						continue;
					}
					if (in_array($item, $d['tags']))
					{
						// echo "$item TAG text in black list!\n";
						$data[$k] = null;
						continue;
					}
				}
			}
		}

		if (mb_strtolower($type) == 'text')
		{
			foreach ($data as $k => $d)
			{
				foreach ($blacklist as $item)
				{
					if ($data[$k] == null)
					{
						continue;
					}
					if (stripos($d['text'], $item) !== false)
					{
						// echo "$item TEXT in black list!\n";
						$data[$k] = null;
						continue;
					}
				}
			}
		}

		$data = array_values(array_filter($data));

		return self::response($data, strtolower($output));
	}

	/**
	 * Создание картинки с текстом.
	 *
	 * Создаёт однотонную картинку с наложенным текстом.
	 * Можно использовать для постов в ВКонтакте, Instagram, Telegram, etc...
	 *
	 * 		[
	 *			'mode' 				=> 'smart',
	 *			'font' 				=> __DIR__ . '/src/assets/fonts/font.ttf',
	 *			'text_size' 		=> '30',
	 *			'background_color' 	=> '#fff',
	 *			'text_color' 		=> '#4f5252',
	 *			'padding' 			=> '60',
	 *			'width' 			=> '720',
	 *			'line_height'		=> '55',
	 *		]
	 *
	 * Используется сторонняя библиотека.
	 * @see https://github.com/Priler/Text2Image
	 *
	 * @param  string	   $text  	 Накладываемый текст
	 * @param  string	   $savePath Путь по которому будет сохраненна картинка
	 * @param  array	   $params   Скоп параметров для создания картинки
	 * @param  string|null $output   Формат ответа
	 *
	 * @return object 		Magic
	 */
	// public static function createImage($text, $path, $params = [])
	// {
	// 	$img = new Priler\Text2Image\Magic($text);

	// 	$img->set_mode($params['mode']);
	// 	$img->add_font('font', $params['font']);
	// 	$img->font = $img->get_font('font');
	// 	$img->text_size = $params['text_size'];
	// 	$img->background_color = $params['background_color'];
	// 	$img->text_color = $params['text_color'];
	// 	$img->padding = $params['padding'];
	// 	$img->width = $params['width'];
	// 	$img->line_height = $params['line_height'];

	// 	$img->save($path, 'jpg', 90);

	// 	return $img;
	// }

	/* Получение контента страницы */
	private static function request($url)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec($ch);

		curl_close($ch);

		return $response;
	}

	/* Ответ */
	private static function response($data, $output)
	{
		switch ($output)
		{
			case 'array':
				return $data;
			case 'json':
				return json_encode($data, JSON_PRETTY_PRINT);
			default:
				return self::response(self::message('Неподдерживаемый формат', 1) , self::DEFAULT_OUTPUT_TYPE);
		}
	}

	/* Формирование сообщения + код */
	private static function message($text = 'Неизвестная ошибка', $code = 99)
	{
		return ['message' => $text, 'code' => $code];
	}
}

