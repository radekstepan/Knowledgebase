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
    <link rel="stylesheet" href="<?php $this->url('/public/toolbar/toolbar.css'); ?>" type="text/css" media="screen"/>

    <script type="text/javascript">
        var swich;
        var categorySwitch = 1;
        var sourceSwitch = 1;
        var typeSwitch = 1;
        
        function selectSwitch(e) {
            var inp = document.getElementById(e+'-input');
            var sel = document.getElementById(e+'-select');
            var lnk = document.getElementById(e+'-link');
            
            // determine switch type and switch over
            switch (e) {
                case 'category':
                    swich = categorySwitch; if (swich) categorySwitch = 0; else categorySwitch = 1; break;
                case 'source':
                    swich = sourceSwitch; if (swich) sourceSwitch = 0; else sourceSwitch = 1; break;
                case 'type':
                    swich = typeSwitch; if (swich) typeSwitch = 0; else typeSwitch = 1; break;
            }

            // toggle
            if (swich) {
                sel.style.display = 'none';
                inp.style.display = 'block';
                inp.focus();
                lnk.innerHTML = '(or select from a list)';
            } else {
                sel.style.display = 'block';
                inp.style.display = 'none';
                lnk.innerHTML = '(or add new)';
            }
        }

        function selectToInput() {
            if (categorySwitch) {
                document.getElementById('category-input').value = document.getElementById('category-select').value;
            }
            if (sourceSwitch) {
                document.getElementById('source-input').value = document.getElementById('source-select').value;
            }
            if (typeSwitch) {
                document.getElementById('type-input').value = document.getElementById('type-select').value;
            }
        }

        function expand() {
            var e = document.getElementById('expand');
            var textarea = document.getElementById('textarea');
            var details = document.getElementById('details');
            if (e.innerHTML == '(expand textarea)') {
                details.style.display = 'none';
                textarea.className = 'expanded';
                e.innerHTML = '(show details)';
            } else {
                details.style.display = 'block';
                textarea.className = '';
                e.innerHTML = '(expand textarea)';
            }
        }
    </script>
    <script type="text/javascript" src="<?php $this->url('/public/toolbar/toolbar.js'); ?>"></script>
</head>
<body>
     <?php if (!empty($messages)): foreach ($messages as $message): ?>
        <h2 class="message <?php echo $message['status']; ?>"><?php echo $message['message']; ?></h2>
    <?php endforeach; endif; ?>
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
                    <li><a href="<?php $this->url('/'); ?>">Knowledgebase</a></li>
                    <li class="current"><a href="<?php $this->url('/new'); ?>">Add New</a></li>
                    <li><a href="<?php $this->url('/browse/tag/star'); ?>">Starred</a></li>
                    <li class="settings"><a href="<?php $this->url('/settings'); ?>">Settings</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div id="main">
        <div class="container">
            <div id="text" class="push-1 span-22 last">
                <div class="span-22 last">                    
                    <form action="<?php $this->url('/new'); ?>" method="post">
                    <h2>Add New Knowledge <input class="button" onclick="selectToInput();"
                                                 type="submit" value="Save" /></h2>
                    <div class="span-11">
                        <?php include 'application/views/textarea.tpl.php'; ?>
                    </div>
                    <div id="details" class="span-11 last">
                        <div class="span-11 last">
                            <label>Title:</label><input type="text" name="title" value="<?php echo $saved['title']; ?>" />
                        </div>
                        <div class="span-11 last">
                            <label>Tags:</label><div class="right">e.g.: personal, research, environment</div>
                            <textarea name="tags"><?php echo $saved['tags']; ?></textarea>
                        </div>
                        <div class="span-11 last">
                            <label>Category:</label>
                            <a id="category-link" href="" onClick="selectSwitch('category');return false;">
                                (or add new)
                            </a>
                            <input id="category-input" style="display:none;" type="text" name="category" value="" />
                            <select id="category-select">
                                <?php foreach ($categories as $category): ?>
                                    <option <?php if ($saved['category'] == $category['value']) echo 'selected'; ?>>
                                        <?php echo $category['value']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="span-11 last">
                            <label>Source:</label>
                            <a id="source-link" href="" onClick="selectSwitch('source');return false;">
                                (or add new)
                            </a>
                            <div class="right">e.g.: John Smith, Wikipedia</div>
                            <input id="source-input" style="display:none;" type="text" name="source" value="" />
                            <select id="source-select">
                                <?php foreach ($sources as $source): ?>
                                    <option <?php if ($saved['source'] == $source['value']) echo 'selected'; ?>>
                                        <?php echo $source['value']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="span-11 last">
                            <label>Type:</label>
                            <a id="type-link" href="" onClick="selectSwitch('type');return false;">
                                (or add new)
                            </a>
                            <div class="right">e.g.: blog article, published paper</div>
                            <input id="type-input" style="display:none;" type="text" name="type" value="" />
                            <select id="type-select">
                                <?php foreach ($types as $type): ?>
                                    <option <?php if ($saved['type'] == $type['value']) echo 'selected'; ?>>
                                        <?php echo $type['value']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="span-11 last">
                            <label>Date:</label><div class="right">e.g.: 2008-12-30</div>
                            <input type="text" name="date" value="<?php echo $saved['date']; ?>" />
                        </div>
                        <div class="span-11 last">
                            <label>Comments:</label>
                            <textarea name="comments"><?php echo $saved['comments']; ?></textarea>
                        </div>
                    </div>
                    </form>
                </div>
                <div class="span-22 last">
                    <center><a id="expand" href="" onClick="expand();return false;">(expand textarea)</a></center>
                </div>
            </div>
        </div>
    </div>
</body>
</html>