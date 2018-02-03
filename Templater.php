<?php

class Templater{
    private $templatesPath, $templateName, $props = [], $cache = false;
    
    //Patterns
    private $valuePattern = '/\{\{(\w+)\}\}/',
        $ifPattern = '/<if.*(cond="(.+)").*>/';
    
    public function __construct($templateName){
        include_once 'TemplaterConfig.php';

        $this->templatesPath = $tc['templatesPath'];

        if(!isset($templateName)){
            throw new Exception("Template name doesn't exist");
        }

        $this->templateName = str_replace('//', '/',$this->templatesPath.$templateName);
    }

    public function assign($prop, $val){
        if(!isset($this->props[$prop])){
            $this->props[$prop] = $val;
        } else{
            throw new Exception("Property $prop alredy exist");
        }
    }

    public function render(){
        $template = $this->props;

        $tplFile = fopen($this->templateName, 'r');
        $tplFileContent = fread($tplFile, filesize($this->templateName));
        fclose($tplFile);

        $this->replaceVars($tplFileContent);
        $this->replaceConditions($tplFileContent);

        $this->writeMatches($tplFileContent);

        // $renderedTemplateName = str_replace('.tmpl', '.php', $this->templateName);

        // file_put_contents($renderedTemplateName, $tplFileContent);

        // include_once $renderedTemplateName;
    }

    private function replaceVars(&$content){
        $valuesMatches = [];
        preg_match_all($this->valuePattern, $content, $valuesMatches);

        for($i = 0; $i < count($valuesMatches[0]); $i++){
            if(!isset($this->props[$valuesMatches[1][$i]])){
                throw new Exception("Property '{$valuesMatches[1][$i]}' or value for it doesn't exist in this template");
            }
            $content = str_replace($valuesMatches[0][$i], '<?php echo "{$template[\''.$valuesMatches[1][$i].'\']}"; ?>', $content);
        }
    }

    private function replaceConditions(&$content){
        $offset = 0;
        
        while(preg_match($this->ifPattern, $content, $matches, PREG_OFFSET_CAPTURE, $offset)){
            $changedCondition = $matches[2][0];
            foreach($this->props as $key => $val){
                if(preg_match('/'.$key.'/', $changedCondition)){
                    $changedCondition = preg_replace('/'.$key.'/', '$template[\''.$key.'\']', $changedCondition);
                }
            }
            $content = substr_replace($content, '<?php if('.$changedCondition.'){ ?>', $matches[0][1], mb_strlen($matches[0][0]));
            $offset = $matches[0][1] + mb_strlen($matches[0][0]);
        }

        while(preg_match('/<\/if>/', $content, $matches, PREG_OFFSET_CAPTURE, $offset)){
            $content = substr_replace($content, '<?php } ?>', $matches[0][1], mb_strlen($matches[0][0]));
            $offset = $matches[0][1] + mb_strlen($matches[0][0]);
        }
    }

    function writeMatches($r){
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/templates/matches.txt', $r);
    }
}