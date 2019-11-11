# Тестовое задание

### Цель задания

Необходимо поднять сервис, который добавляет пользователя в БД по запросу из очереди и выдаёт результат обработки в другую очередь.

Технологический стек:
- Docker
- Symfony 4
- RabbitMQ
- PostgreSQL

## Описание компонентов
### Docker
В файле docker_compose.yaml заданы контейнеры php, db и rabbit-mq, имена контейнеров задаются переменными в .env файле.
Сборка и запуск Docker выполняется скриптом build.sh, работа которого завершается листингом debug информации из работающего consumer контейнера php.

### PostgreSQL
Находится в контейнере db. Используется официальный образ PostgreSQL для Docker. Параметры для контейнера и бд задаются в переменных файла .env.
Структура бд:
```javascript
id character(10) NOT NULL
name character(100) NOT NULL
email character(100) NOT NULL
location character(100) NOT NULL
```
### RabbitMQ
Находится в контейнере rabbit-mq. Используется официальный образ RabbitMQ для Docker, работает админ панель. Параметры для контейнера и RabbitMQ задаются в переменных файла .env.

### Сервис добавления пользователей
Сервис написан на фреймворке Symfony 4. Располагается в контейнере php. Параметры для контейнера и сервиса задаются в переменных файла .env.

При запуске докера через скрипт build.sh выполняются команды:

    composer install
    doctrine:migrations:migrate
    rabbitmq:setup-fabric
    rabbitmq:consumer

А так же копируется .env файл в рабочую директорию сервиса для доступа к переменным окружения.

## Описание сервиса.
Для работы с очередями используется готовый бандл для работы RabbitMQ и библиотека из состава бандла PhpAmqpLib.
После запуска сервис начинает слушать exchange и queue, указанные в переменных файла .env
Вся работа с очередями реализованна в двух контроллерах зарегестрированных как сервисы в файле app/config/services.yaml.
Сервис add_user_service указан в качестве callback для consumer бандла RabbitMQ и реализует логику consumer. 
Сервис reply_to_service передается в качестве аргумента в сервис add_user_service и средствами PhpAmqpLib реализует логику producer для отправки сообщений в другие очереди.
Для работы с бд используется Doctrine.
Для валидации используется Validator, правила валидации указаны в аннотациях к модели.
Так же добавлена кастомная генерация id модели User для соответствия требованиям схемы БД

### Логика работы
Consumer сервиса слушает exchange и queue заданные в переменных .env файла.
Запрос на добавление приходит в виде словаря:
```javascript
  "action": "add_user",
  "name": "some_name",
  "email": "some_email",
  "location": "some_city",
  "reply_to": {
		"exchange": "some_exchange",
		"queue" : "some_queue"
  }
```
Гарантируется, что во входящем запросе всегда есть поле reply_to с правильной структурой. Присутствие и правильность других полей не гарантируется.

После валидации результат отправляется в exchange и queue согласно роутингу в reply_to.
В случае успешной валидации отправляется словарь вида:
```javascript
'id': 'id человека',
'error_code' : 0,
'error_msg' : ""
```
В случае неудачи:
```javascript
'id': 'null',
'error_code' : 1,
'error_msg' : 'error description'
```
### Как работать с сервисом
Для запуска необходимо выполнить скрипт build.sh из коневой директории, он соберет докер и запустит приложение.
Для взаимодействия с сервисом необходимо использовать админ панель RabbitMQ, логин, пароль и порт так же задаются в переменных .env файла.
После входа необходимо выбрать очередь указанную в .env файле для consumer и через форму отправить сообщение со словарём в формате json. Дебаг информация сразу будет доступна из консоли, если скрипт build.sh не был прекращен, так же согласно роутингу будут созданы exchange и очереди, посмотреть содержимое которых можно так же из админ панели RabbitMQ.
Так же можно добавлять consumer запуская скрипт add_consumer.sh

# От автора
До этого задания я ни разу не работал с symfony 4 и другими версиями, так же я не работал и с Docker. На освоение документации и выполнения работы мне понадобилось около 14 часов. Сборка Docker тестировалась на Debian и Ubuntu.
