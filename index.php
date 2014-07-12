<?php if( !isset($_GET['path']) ) {  ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>localhost</title>
    <style type="text/css">
    	html     { overflow-y: scroll; }
        body     { font-family:'Lucida Sans Unicode', 'Lucida Grande', 'Verdana', 'Geneva', 'Arial', 'Helvetica', sans-serif; color: #333; }

        table    { width: 100%; border-collapse: collapse; border-spacing: 0; border: 1px solid #d8d8d8; background: #f8f8f8; font-size: 15px; }
        tr       { border-bottom: 1px solid #eee; } tr:last-of-type { border-bottom: 1px solid #d8d8d8; }
        thead    { color: #4e575b; background: #e6f1f6; text-align: left; }
        thead tr { border: 1px solid #d8d8d8; }
        td, th   { color: #888; padding: 5px 7px; white-space: nowrap; }

        a        { color: #4183c4; text-decoration: none; cursor: pointer; }
        a.active { font-weight: bold; }
        a:hover  { text-decoration: underline; }
        a.index  { display: block; width: 100%; text-decoration: none; width: 20px;}

        .icon        { width: 100%; background-repeat: no-repeat; background-position: center center; }
        .icon.folder { background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA4AAAAMCAYAAABSgIzaAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAAadEVYdFNvZnR3YXJlAFBhaW50Lk5FVCB2My41LjExR/NCNwAAACZJREFUKFNjaFh29j8uDAQMuDBYIzZAlEbS8dX/oxpx4pGg8ep/AP5m9PwXpiKYAAAAAElFTkSuQmCC); }
        .icon.file   { background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA4AAAAOCAYAAAAfSC3RAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAAadEVYdFNvZnR3YXJlAFBhaW50Lk5FVCB2My41LjExR/NCNwAAAENJREFUOE/tjUEKACAIBP21z99aqDCJTPDYwCCIgwJAVBWRvLOuMMLHzyGx8RZyej1jl/tIakPOm/Om8z+eqAszApAGWB51QcSH0LAAAAAASUVORK5CYII=); }
        .icon.xlink  { background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA4AAAAOCAYAAAAfSC3RAAAABGdBTUEAALGPC/xhBQAAAAlwSFlzAAAOwgAADsIBFShKgAAAABp0RVh0U29mdHdhcmUAUGFpbnQuTkVUIHYzLjUuMTFH80I3AAAAW0lEQVQ4T9WQQQrAMAgE/b1nPyD4Ib+UdA+WYsS0uVWYkzssSsw8MtcQcPdlF7RiYGZL5hZz+MmRWEmgFV/dmKXcJCLIYEhV28ZSCnY3lhLYPicT+T+KXzkUeUyx3ybVSJSS9gAAAABJRU5ErkJggg==); } 

        .wrapper    { margin: 30px auto 20px; border: 1px solid #eee; border-left: none; border-right: none; background-color: #fbfbfb;}
        #folder-nav { width: 900px; margin: 0 auto; padding: 10px 0; font-size: 21px; font-weight: normal;  }
        #container  { width: 900px; margin: 0 auto; }     
    </style>
</head>

<body>

    <div class="wrapper">
        <div id="folder-nav">&nbsp;</div>
    </div>

    <div id="container">
        <table>
            <thead>
                <tr>
                    <th style="width:20px;"></th> <!-- index link -->
                    <th style="width:20px;"></th> <!-- icon -->
                    <th style="width:450px;">Filename</th>
                    <th>Type</th>
                    <th>Size</th>
                    <th>Modified</th>
                </tr>
            </thead>
            <tbody id="folder-contents">
            </tbody>
        </table>
    </div>

    <script type="text/javascript">
    (function() { // On document ready

        var storedHash = window.location.hash;

        hashChanged(storedHash)
        
        if ('onhashchange' in window) { // event supported?
            window.onhashchange = function () {
                hashChanged(window.location.hash);
            }
        } else{ // event not supported:
            var storedHash = window.location.hash;
            window.setInterval(function () {
                if (window.location.hash != storedHash) {
                    storedHash = window.location.hash;
                    hashChanged(storedHash);
                }
            }, 100);
        }
        
        function hashChanged(hash){
            var path = hash.substr(2);
            update_table(path); 
        }
        

        function update_table(path_string)
        {
            var xmlhttp = new XMLHttpRequest();

            xmlhttp.open('GET', 'index.php?path='+path_string, true);
            xmlhttp.onreadystatechange = function() {
                if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                    var obj = JSON.parse(xmlhttp.responseText);
                    document.getElementById('folder-nav').innerHTML = obj.nav;
                    document.getElementById('folder-contents').innerHTML = obj.tbody;
                }
            }
            xmlhttp.send();
        }
    })(); // End document ready
    </script>
</body>
</html>

<?php } else {
// If is AJAX call, process query path and return json table data

    function hasIndex($path, $filename)
    {
        $abs = $path.'\\'.$filename;

        if(file_exists($abs))
        {
            $info = pathinfo($abs);

            if (is_dir($abs)) {
                $dir = new DirectoryIterator($abs);
                foreach ($dir as $file) {
                    if (!$file->isDot()) {
                        $info = pathinfo($file);
                        if ( strtolower($info['filename']) == 'index' && !$file->isDir()) { return true; } 
                    }
                }
            }
            else { if (strtolower($info['filename']) == 'index') { return true; } }
        }
        return false;
    }

    function compare($a, $b)
    {
        if ( $a['folder'] == $b['folder'] ) { return strcasecmp($a["filename"], $b["filename"]);  }
        return $a['folder'] ? -1 : 1; 
    }

    function formatBytes($bytes, $precision = 2) { 
        $units = array('B', 'KB', 'MB', 'GB', 'TB'); 
        $bytes = max($bytes, 0); 
        $pow   = floor(($bytes ? log($bytes) : 0) / log(1024)); 
        $pow   = min($pow, count($units) - 1); 
        $bytes/= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow]; 
    }

    function last($array, $key){
        end($array);
        return $key === key($array);
    }


    function getDirTable($path_array)
    {
        $data = array();  // data to return      
        $cwd  = getcwd();
        $host = $_SERVER['SERVER_NAME']; // hostname, eg. localhost

        $wdRel    = substr(dirname($_SERVER['SCRIPT_NAME']), 1); // relative working directory
        $wdRelArr = empty($wdRel) ? false : explode('/', $wdRel);
        $wdCount  = $wdRelArr ? count($wdRelArr) : 0;

        if ($wdCount > 0)
        {
            $data['nav'] = $host;
            foreach ($wdRelArr as $key => $folder)
            {
                if (last($wdRelArr, $key))
                    { $data['nav'] .= ' / <a href="#/">'.$folder.'</a>'; }
                else
                    { $data['nav'] .= ' / '.$folder; }
            }
        }
        else { $data['nav'] = '<a href="#/">'.$host.'</a>'; }

        $rel  = '';

        if (count($path_array) > 0) 
        { 
            foreach ($path_array as $key => $folder)
            {
                $rel   .= $folder.'/';
                $class = last($path_array, $key) ? ' class="active" ' : '';
                $data['nav'] .= ' / <a href="#/'.$rel.'"'.$class.'>'.$folder.'</a>';
            } 
        }

        $path = $cwd.'/'.$rel;
        $path = str_replace('/', '\\',  $path);

        $dir = new DirectoryIterator($path);
        $dir_data = array();
        if(file_exists($dir) && is_dir($dir))
        {
            foreach ($dir as $file)
            {
                if (!$file->isDot()) 
                {
                    $modified = date("Y-m-d H:i:s", $file->getMTime());                    
                    $filename = $file->getFilename();
                       $index = hasIndex($path, $filename);
                        $size = $file->isDir() ? '' : formatBytes($file->getSize());

                    $dir_data[] = array(
                        'index'    => $index,                // false, true(is index file or is a folder and has index file)
                        'folder'   => $file->isDir(),        // true or false
                        'filename' => $filename,             // file.ext
                        'extension'=> $file->getExtension(), // .ext or false
                        'size'     => $size,                 // size in bytes
                        'modified' => $modified              // timestamp
                    ); 
                }
            }
            usort($dir_data, "compare");

            $data['tbody'] = '';
            $temp = array();
            foreach ($dir_data as $file)
            {
                $temp['index'] =  $file['index'] ? '<a href="'.$rel.$file['filename'].'" target="_blank" class="index" title="Index"><div class="icon xlink">&nbsp;</div></a>'  : '';
                $temp['icon']  = $file['folder'] ? '<div class="icon folder">&nbsp;</div>' : '<div class="icon file">&nbsp;</div>';
                $temp['type']  = $file['folder'] ? '' : $file['extension'];

                if ($file['folder'])  { $temp['anchor'] = '<a href="#/'.$rel.$file['filename'].'">'.$file['filename'].'</a>'; }
                else                  { $temp['anchor'] = '<a href="'.$rel.$file['filename'].'" target="_blank">'.$file['filename'].'</a>'; }
                
                $data['tbody'] .= '<tr><td>'.$temp['index'].'</td><td>'.$temp['icon'].'</td><td>'.$temp['anchor'].'</td><td>'.$temp['type'].'</td><td>'.$file['size'].'</td><td>'.$file['modified'].'</td></tr>';
            }

            if (count($dir_data) == 0){ $data['tbody'] = '<tr><td colspan="6" style="text-align:center;">Empty folder.</td></tr>'; }
        }
        else { $data['tbody'] = '<tr>Folder not found<tr>'; }

        return $data;
    } 

    $path_string = $_GET['path'];


    $arrPath = array();

    if (!empty($path_string)) {
        $arrPath = explode('/', $path_string);
        foreach ($arrPath as $key => $value) 
        { 
            if (empty($value)) { unset($arrPath[$key]); } 
        } 
    }

    $arr = getDirTable($arrPath);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($arr);
    exit();
    
} 

?>


