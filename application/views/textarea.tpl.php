<?php if (!defined('FARI')) die(); ?>

<ul id="toolbar">
    <li class="toolbar bold"><a onclick="toolbar.tag('bold');" title="Bold">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a></li>
    <li class="toolbar italics"><a onclick="toolbar.tag('italics');" title="Italics">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a></li>
    <li class="toolbar underline"><a onclick="toolbar.tag('underline');" title="Underline">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a></li>
    <li class="toolbar list"><a onclick="toolbar.tag('bullet');" title="Bullet List">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a></li>
    <li class="toolbar para"><a onclick="toolbar.tag('para');" title="Paragraph">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a></li>
    <li class="toolbar h1"><a onclick="toolbar.tag('h1');" title="Main Header">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a></li>
    <li class="toolbar h2"><a onclick="toolbar.tag('h2');" title="Sub Header">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a></li>
    <li class="toolbar h3"><a onclick="toolbar.tag('h3');" title="Sub Header">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a></li>
    <li class="toolbar quote"><a onclick="toolbar.tag('bq');" title="Quotation">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a></li>
    <li class="toolbar img"><a onclick="toolbar.tag('img');" title="Image">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a></li>
    <li class="toolbar link"><a onclick="toolbar.tag('link');" title="Hyperlink">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a></li>
</ul>
<textarea id="textarea" name="textarea"><?php echo $saved['textarea']; ?></textarea>