function Textile() {
    this.target = 'textarea';

    this.tag = function(tag) {
        // set the destination form
        var article = document.getElementById(this.target);
        // initialize vars
        var a, b = '';

        // check which tag was used
        switch(tag) {
            // paragraph
            case 'para':
                a = 'p. ';
                b = '\n';
                break;

            // heading 1
            case 'h1':
                a = 'h1. ';
                b = '\n';
                break;

            // heading 2
            case 'h2':
                a = 'h2. ';
                b = '\n';
                break;

            // blockquote
            case 'bq':
                a = 'bq. ';
                b = '\n';
                break;

            // bullet list
            case 'bullet':
                var usr = window.prompt("How many entries in the list?"); // display prompt window
                a = '';
                var i = 0;
                while(i < usr) {
                    a += '* \n';
                    i++;
                }
                b = '';
                break;

            // strong (bold) text
            case 'bold':
                a = '*';
                b = '*';
                break;

            // underline (ins) text
            case 'underline':
                a = '+';
                b = '+';
                break;

            // italics text
            case 'italics':
                a = '_';
                b = '_';
                break;

            // image
            // !imageurl!
            case 'img':
                var usr = window.prompt("What is the image URL?"); // display prompt window
                a = '!'+usr+'!';
                b = '';
                break;

            // hyperlink
            // "linktext":url
            case 'link':
                var linktext = window.prompt("What is the text for the link?"); // display prompt window
                var url = window.prompt("What is the link URL?"); // display prompt window
                a = '"'+linktext+'":'+url;
                b = ' ';
                break;
        }

        // Firefox code
        if(!article.setSelectionRange) {
            var selection = document.selection.createRange().text;

            article.focus();

            if(selection.length = 0) {
                article.value += a;
            } else {
                var output = a + selection + b;
            }
            // Internet Explorer code
            document.selection.createRange().text = output;
        } else {
            // get everything from start of article to start of selection
            var begin = article.value.substring(0, article.selectionStart);
            selection = a + article.value.substring(article.selectionStart, article.selectionEnd) + b;
            var end = article.value.substring(article.selectionEnd, article.value.length);

            article.value = begin + selection + end;
        }

        // give focus back to the textarea
        article.focus();
    }
}

var toolbar = new Textile();