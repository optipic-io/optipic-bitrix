# 1С-Битрикс / OptiPic
Модуль 1С-Битрикс для интеграции с [OptiPic.io](https://optipic.io/ru/cdn/) (сервис для автоматической оптимизации и сжатия изображений на сайте)

<sup>|</sup>
<sup>[English](https://optipic.io/en/webp/bitrix/) | </sup>
<sup>[Español](https://optipic.io/es/webp/bitrix/) | </sup>
<sup>[Deutsch](https://optipic.io/de/webp/bitrix/) | </sup>
<sup>[Türk](https://optipic.io/tr/webp/bitrix/) | </sup>
<sup>[Français](https://optipic.io/fr/webp/bitrix/) | </sup>
<sup>[Italiano](https://optipic.io/it/webp/bitrix/) | </sup>
<sup>[Português](https://optipic.io/pt/webp/bitrix/) | </sup>
<sup>[Polski](https://optipic.io/pl/webp/bitrix/) | </sup>
<sup>[Čeština](https://optipic.io/cz/webp/bitrix/) | </sup>
<sup>[Русский](https://optipic.io/ru/webp/bitrix/) | </sup>
<sup>[Беларуская](https://optipic.io/by/webp/bitrix/) | </sup>
<sup>[中国](https://optipic.io/cn/webp/bitrix/) | </sup>
<sup>[日本](https://optipic.io/jp/webp/bitrix/) | </sup>
<sup>[বেঙ্গল](https://optipic.io/bn/webp/bitrix/) | </sup>
<sup>[한국인](https://optipic.io/ko/webp/bitrix/) | </sup>

**Настоятельно рекомендуем использовать [официальный модуль из Маркетплейс](https://marketplace.1c-bitrix.ru/solutions/step2use.optimg/) - он всегда является более актуальным и стабильным**

## Установка и использование
1. [Зарегистрируйте](https://optipic.io/ru/register/?cdn) аккаунт на сайте [OptiPic.io](https://optipic.io/ru/cdn/).
1. Добавьте ваш сайт в [личный кабинет CDN](https://optipic.io/ru/cdn/cp/).
1. Скопируйте **ID** сайта из личного кабинета CDN.
1. [Скачайте](https://marketplace.1c-bitrix.ru/solutions/step2use.optimg/) и [установите](https://youtu.be/7L0nf0w299M) модуль на свой сайт.  
Если вы скачиваете модуль с github, положите папку `step2use.optimg` из репозитория в папку `/bitrix/modules/` на своем сайте.
1. Сохраните **ID** вашего сайта в настройки установленного модуля.
1. Включите автозамену URL изображений в настройках модуля.

**Внимание!**  
Если ваш сайт работает в кодировке, отличной от `windows-1251` - перекодируйте все языковые файлы интерфейса в папке с модулем.  
Языковые файлы находятся в папке `lang`.  
Пример, как можно перекодирововать файл `file.php` в `UTF-8` через консоль Linux:  
`$ iconv -f cp1251 -t utf8 file.php -o file.php`  
