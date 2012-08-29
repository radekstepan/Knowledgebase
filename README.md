# Knowledgebase (January 2010)

Because Interspire Knowledge Manager costs at least $495 per year

![image](https://github.com/radekstepan/Knowledgebase/raw/master/example.png)

One of the Fari MVC projects giving **relevant & ranked** search results you need. Porter Stemming Algorithm is used to generate sets of keywords with different weights stored in a fast SQLite database. Your keywords are highlighted in search results and you can save these into a starred set, useful when doing a research.

The interface sports six different themes to match your topic and mood.

## Getting started

Database access is configured to use `pdo_sqlite` by default, you can check its existence like so:

```php
<?php
phpinfo();
?>
```

Visit [127.0.0.1/knowledgebase](http://127.0.0.1/knowledgebase) to add/view/edit articles, no authentication & authorization is required.

## Troubleshooting

On PHP 5.4+ you will get "call-time pass-by-reference" error.

Fari Framework automatically understands that you are in development mode, if you call the app from `127.0.0.1`. Do so to see a stacktrace of where an error has happened instead of seeing a placeholder error message.