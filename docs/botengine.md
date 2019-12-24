# Класс BotEngine
Сердце движка. Рассмотрим основные методы.

## addCommand & addCommands
Эти методы используются для добавления новых команд. addCommand — для одной команды, addCommands — для нескольких команд с одним обработчиком.

| Параметр       | Тип            | Описание                                                                                      |
| ---------------|----------------|-----------------------------------------------------------------------------------------------|
| $name / $names | string / array | Название команды или массив с названиями команд (для addCommand и addCommands соответственно) |
| $handler       | callable       | Функция-обработчик. Будет вызвана при получении команды                                       |

```php
$BotEngine->addCommand('привет', function($data) {
  call('messages.send', ['peer_id' => $data['object']['message']['peer_id'], 'message' => 'Привет!', 'random_id' => 0]);
  return true;
});

$BotEngine->addCommands(['Команда', 'Раз', 'Два', 'Три'], function($data) {
  call('messages.send', ['peer_id' => $data['object']['message']['peer_id'], 'message' => 'Вот такие вот дела', 'random_id' => 0]);
  return true;
});
```

Функция-обработчик всегда должна возвращать `true`, иначе будет запущен дефолтный обработчик. Его также можно изменить:
```php
$BotEngine->addCommand('default', function($data) {
  call('messages.send', ['peer_id' => $data['object']['message']['peer_id'], 'message' => 'Я не знаю, как на это ответить', 'random_id' => 0]);
  return true;
});
```

> Вы можете задать режим строгого соблюдения регистра. Для этого достаточно при создании экземпляра класса BotEngine передать единственным аргументом в конструктор `false`.

## runCommand
Проверяет, существует ли команда, и запускает её.

| Параметр | Тип    | Описание                                                                                                                                           |
| ---------|--------|----------------------------------------------------------------------------------------------------------------------------------------------------|
| $name    | string | Название команды. Лучше создавать команды по первому слову, поэтому рекомендуется передавать `explode(' ', $data['object']['message']['text'])[0]` |
| $data    | array  | Данные, полученные от CB или от LP                                                                                                                 |

```php
$BotEngine->runCommand('test', $dataFromCB);
```

## addPayloadCommands
Добавление команды, которая будет определяться по значению поля command в payload.

| Параметр | Тип      | Описание                                                    |
| ---------|----------|-------------------------------------------------------------|
| $names   | string   | Названия команд. Можно передать одно значение или несколько |
| $handler | callable | Функция-обработчик                                          |

```php
$BotEngine->addPayloadCommands(['start'], function($data) {
  // Ответ на нажатие кнопки Начать
});

$BotEngine->addPayloadCommands(['main', 'menu'], function($data) {
  // Например, открыть клавиатуру с меню
});
```

> Эта функция добавляет команду, которая будет реагировать на значение поля `command` в `payload` сообщения, т.е. `json_decode($data['object']['message']['payload'], true)['command']`. Следовательно, вы должны использовать слово `command` в качестве имени поля, т.е. JSON должен иметь вид `{"command": %название%}`

## addCommandsAlias
Добавляет алиас для payload-команды.

| Параметр     | Тип    | Описание                                                             |
| -------------|--------|----------------------------------------------------------------------|
| $payloadName | string | Название payload-команды                                             |
| $textName    | string | Название текстовой команды (которая определется по тексту сообщения) |

```php
$be->addCommandsAlias('start', 'меню'); // Нажатие кнопки с payload = {"command": "start"} будет эквивалентно сообщению с text = меню
```

## checkAllCommands
Проверяет, есть ли команда какого-либо типа. При этом приоритет имеют payload-команды.

| Параметр     | Тип    | Описание                   |
| -------------|--------|----------------------------|
| $payloadName | string | Название payload-команды   |
| $textName    | string | Название текстовой команды |
| $data        | array  | Сообщение                  |

```php
$BotEngine->checkAllCommands('start', 'меню', $data);
```

## addDataHandler
Добавление обработчика для событий, отличных от `message_new`.

| Параметр | Тип      | Описание                                                |
| ---------|----------|---------------------------------------------------------|
| $name    | string   | Название события                                        |
| $handler | callable | Функция-обработчик. Будет вызвана при получении события |

```php
$BotEngine->addDataHandler('group_leave', function($data) {
	echo '-1 subscriber :(';
});
```

## И ещё кое-что
С версии `0.5.1` вы можете самостоятельно отключать обработку команд в определенных случаях. [Добавьте DataHandler](#adddatahandler) для события `message_new`, функция которого будет возвращать `false`. В таком случае обработка команд будет пропущена.

```php
$be->addDataHandler('message_new', function($data) {
  if($data['object']['message']['peer_id'] == 1) {
    // Это же Дуров!

    return false;
  }

  // Это не Дуров, проверяем команды

  return true;
});
```

С версии `0.6` движок больше не устанавливает ответ на неизвестную команду самостоятельно. Это значит, что если вы не задали обработчик неизвестной команды, движок больше не будет отправлять сообщение «Unknown command».