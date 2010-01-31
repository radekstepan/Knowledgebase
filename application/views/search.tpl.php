<?php if (!defined('FARI')) die(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title><?php echo $settings['title']['value']; ?> Knowledgebase</title>
    <link rel="shortcut icon" type="image/x-icon" href="<?php $this->url('/public/favicon.ico'); ?>">

    <link rel="stylesheet" href="<?php $this->url('/public/grid/screen.css'); ?>" type="text/css" media="screen, projection"/>
    <link rel="stylesheet" href="<?php $this->url('/public/grid/print.css'); ?>" type="text/css" media="print"/>
    <!--[if lt IE 8]>
        <link rel="stylesheet" href="<?php $this->url('/public/grid/ie.css'); ?>" type="text/css" media="screen, projection"/>
    <![endif]-->
    <link rel="stylesheet" href="<?php $this->url('/public/'.$settings['theme']['value'].'.css'); ?>" type="text/css" media="screen"/>

    <script type="text/javascript">
        function searchFocus() {
            var q = document.getElementById('q');
            if (q.value == 'Search Knowledgebase') {
                q.className = 'text focused'; q.value = '';
            }
        }
        function searchBlur() {
            var q = document.getElementById('q');
            if (q.value == '') {
                q.className = 'text'; q.value = 'Search Knowledgebase';
            }
        }
        function search() {
            var query = document.getElementById('q').value;
            location.href = '<?php $this->url('/search/results/'); ?>'
                + query.replace(/[^a-zA-Z 0-9]+/g, '').replace(/\s+/g, '-').toLowerCase();
        }
    </script>
</head>
<body>
    <div id="header">
        <div class="container">
            <div class="span-14">
                <h1><?php echo $settings['title']['value']; ?> <span>Knowledgebase</span></h1>
            </div>
            <div class="span-10 last">
                <p>
                    <strong><?php echo $_SESSION['Fari\Benchmark\Queries'] ;?></strong> database queries using
                    <strong><?php echo Fari_Benchmark::getMemory() ;?></strong> of memory in
                    <strong><?php echo Fari_Benchmark::getTime() ;?></strong>
                </p>
            </div>
        </div>
    </div>
    <div id="menu">
        <div class="container">
            <div class="span-24">
                <ul>
                    <li class="current"><a href="<?php $this->url('/'); ?>">Knowledgebase</a></li>
                    <li><a href="<?php $this->url('/new'); ?>">Add New</a></li>
                    <li><a href="<?php $this->url('/browse/tag/star'); ?>">Starred</a></li>
                    <li class="settings"><a href="<?php $this->url('/settings'); ?>">Settings</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div id="main">
        <div class="container">
            <div id="search" class="push-1 span-22 last">
                <form action="<?php $this->url('/search/results'); ?>" method="get">
                    <input id="q" name="q" type="text" class="text" onFocus="searchFocus();" onBlur="searchBlur();"
                           value="Search Knowledgebase" />
                    <input class="button" type="submit" value="Search" onClick="search();return false;" />
                </form>
            </div>

            <div id="text" class="push-1 span-22 last">
                <div class="span-22 last">
                    <h2>Browse Categories</h2>
                </div>
                <?php if (!empty($categories)): $i=0; foreach ($categories as $category): ?>
                    <div class="browse span-7 <?php if ($i % 3 == 0) echo 'last'; ?>">
                        <a href="browse/category/<?php echo $category['slug']; ?>"><?php echo $category['value']; ?></a>
                    </div>
                <?php $i++; endforeach; else: ?>
                    <div id="view"><p>No categories yet.</p></div>
                <?php endif; ?>

                <div class="span-22 last">
                    <h2>Browse Sources</h2>
                </div>
                <?php if (!empty($sources)): $i=0; foreach ($sources as $source): ?>
                    <div class="browse span-7 <?php if ($i % 3 == 0) echo 'last'; ?>">
                        <a href="browse/source/<?php echo $source['slug']; ?>"><?php echo $source['value']; ?></a>
                    </div>
                <?php $i++; endforeach; else: ?>
                    <div id="view"><p>No sources yet.</p></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>