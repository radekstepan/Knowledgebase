<?php if (!defined('FARI')) die(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Browsing "<?php echo $title['value']; ?>"</title>
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
                    <li <?php if ($title['value'] == 'Starred') echo 'class="current"'; ?>>
                        <a href="<?php $this->url('/browse/tag/star'); ?>">Starred</a></li>
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
                    <h2>Browsing "<?php echo $title['value']; ?>"</h2>
                </div>
                
                <?php if (!empty($paginator['items'])): foreach ($paginator['items'] as $row): ?>
                    <div class="span-22 last row">
                        <h3><a class="star <?php echo $row['starred']; ?>"
                               href="<?php $this->url('/text/star/' . $row['slug']); ?>">&nbsp;</a>
                            <a href="<?php $this->url('/text/view/' . $row['slug']); ?>">
                            <?php echo $row['title']; ?>
                        </a></h3>
                        <p class="preview">
                            <?php echo substr(Fari_Escape::text(Fari_Textile::toHTML($row['text'])), 0, 300); ?>
                            &hellip;</p>
                        <div class="description">
                            <?php if ($browse == 'category'): ?>
                                <a href="<?php $this->url('/browse/source/' . $row['sourceSlug']); ?>">
                                    <?php echo $row['source']; ?>
                            <?php elseif ($browse == 'source'): ?>
                                <a href="<?php $this->url('/browse/category/' . $row['categorySlug']); ?>">
                                    <?php echo $row['category']; ?>
                            <?php else: ?>
                                <a href="<?php $this->url('/browse/category/' . $row['categorySlug']); ?>">
                                    <?php echo $row['category']; ?></a> &middot;
                                <a href="<?php $this->url('/browse/source/' . $row['sourceSlug']); ?>">
                                    <?php echo $row['source']; ?>
                            <?php endif; ?>
                            </a> &middot;
                            <?php echo Fari_Format::age($row['date']); ?> &middot;
                            <span class="small"><?php echo $row['tags']; ?></span>
                        </div>
                        <div class="span-2"></div>
                    </div>
                <?php endforeach; else: ?>
                    <div id="view"><p>Nothing to display.</p></div>
                <?php endif; ?>

                <?php if ($title['value'] != 'Starred'): ?>
                <div class="span-22 last pagination">        
                    <?php foreach ($paginator['paginator'] as $page): ?>
                        <a class="<?php if ($page['class'] == 'current') echo 'current'; ?>"
                           href="<?php $this->url('browse/' . $browse . '/' . $title['slug'] . '/' . $page['number']) ;?>"
                           ><?php echo $page['number']; ?></a> &nbsp;
                    <?php endforeach ;?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>