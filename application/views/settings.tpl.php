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

    <?php foreach ($themes as $theme): ?>
        <link rel="<?php if ($theme != $settings['theme']['value']): ?>alternate <?php endif; ?>stylesheet"
              title="<?php echo $theme; ?>" href="<?php $this->url('/public/'.$theme.'.css'); ?>" type="text/css"
              media="screen"/>
    <?php endforeach; ?>

    <script type="text/javascript">
        function themeSwitch() {
            var e = document.getElementById('css');
            
            if (document.styleSheets) {
                var c = document.styleSheets.length;
                for (var i = 0; i < c; i++) {
                    if (document.styleSheets[i].title != '') {
                        if (document.styleSheets[i].title != e.value) document.styleSheets[i].disabled = true;
                        else document.styleSheets[i].disabled = false;
                    }
                }
            }
        }
    </script>
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
                    <li><a href="<?php $this->url('/new'); ?>">Add New</a></li>
                    <li><a href="<?php $this->url('/browse/tag/star'); ?>">Starred</a></li>
                    <li class="settings current"><a href="<?php $this->url('/settings'); ?>">Settings</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div id="main">
        <div class="container">
            <div id="text" class="push-1 span-22 last">
                <div class="span-22 last">                    
                    <form action="<?php $this->url('/settings'); ?>" method="post">
                    <h2>Settings <input class="button" type="submit" value="Save" /></h2>
                    <div id="details" class="span-11 last">
                        <div class="span-11 last">
                            <label>Title:</label><div class="right">displayed in the header and browser title</div>
                            <input name="title" id="title" value="<?php echo $settings['title']['value']; ?>" />
                        </div>
                        <div class="span-11 last">
                            <label>CSS Theme:</label><div class="right">placed in the '/public' directory</div>
                            <select name="css" id="css" onChange="themeSwitch();">
                                <?php foreach ($themes as $theme): ?>
                                    <option <?php if ($theme == $settings['theme']['value']) echo 'selected' ?>>
                                        <?php echo $theme; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="span-22 last">
                        <div class="span-22 last">
                            <label>Theme Preview:</label>
                            
                            <h3><a class="star full" href="#">&nbsp;</a>
                                <a href="#">
                                Enhancing Muscle Gain & Recovery</a>
                            </h3>
                            <p class="preview">
                                Top Nutritional Supplements for Muscle Gain & Recovery
                                The following table shows the nutritional supplements that are most often recommended for
                                enhancing muscle gain & recovery. Follow the links within this table to learn more about
                                specific nutrients, or use Nutros&#39; Supplement Solution Tool &hellip;</p>
                            <div class="description">
                                <a href="#">Performance Overviews</a> &middot;
                                <a href="#">Nutritional Supplement Review (Nutros)</a> &middot;
                                a week ago &middot;
                                <span class="small">muscle gain, recovery, supplements, performance</span>
                            </div>
                            <div id="score" class="span-2">
                                <a title="6.64">
                                    <div class="full">&bull&bull&bull</div><div class="empty">&bull&bull</div>
                                </a>
                            </div>
                        </div>

                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>