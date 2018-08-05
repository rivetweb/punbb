
# Инструменты разработки для punbb

## Настройка и использование локального окружения для разработки

### Встроенный PHP сервер для Windows

- cкачать нужную версию PHP http://windows.php.net/download/
- распаковать, например в папку пользователя `C:/Users/user/php`
- прописать путь к PHP в переменную окружения PATH или добавить полный путь в скриптах (.start.bat, .start-custom-ini.bat)
- скачать MySQL с http://dev.mysql.com/downloads/mysql/ и установить
- запустить `.start.bat` или `.start-custom-ini.bat`

### Встроенный PHP сервер для Linux

- установить PHP, MySQL через пакетный менеджер
- команды запуска `.start` или `.start-custom-ini`

## Структура расширения для поддержки динамической инициализации хуков 

На этапе разработки позволяет исключить установку/удаление расширений или очистку кеша хуков при изменении кода. Так же позволяет редактировать код хуков в привычном виде и использовать все возможности редакторов PHP-кода и IDE.

Код хуков подгружается из каждой папки .hooks (у каждого расширения своя папка). Код разделяется по файлам, 1 тип хука - 1 файл:

    .hooks/
        es_essentials.php
        ft_end.php
        hd_head.php
        hd_pre_template_loaded.php
        he_main_output_start.php
        he_new_section.php

Массив $ext_info при данном способе не доступен. Обрабатываются только установленные расширения.

## Утилита .update-manifest.php

Пригодится для подготовки файла manifest.xml расширения. Атоматически заполняет раздел `<hooks>` по содержимому в папке расширения .hooks/

Использование из консоли из папки расширения:

```
cd /path-to-forum/extensions/some_extension
php ../devtools/.update-manifest.php
```

## Отладочная информация

Выводится в консоль браузера если добавлены параметры:

`&show_sql` - вывод списка запросов и время выполнения

`&show_includes` - вывод списка подключаемых файлов страницы (выводятся все встречаемые инструкции подключения файлов для текущего файла скрипта с учетом вложенности подключений)

`&show_hooks` - вывод списка вызываемых хуков страницы

`&show_liveupdate_hooks` - вывод лога подмены хуков (загружаемых из файлов)

`&show_vars` - вывод списка глобальных переменных

`&show_vars=*` - вывод всех значений глобальных переменных

`&show_vars=forum_hooks` - пример вывода значения глобальной переменной forum_hooks