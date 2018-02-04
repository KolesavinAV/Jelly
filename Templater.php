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
        $this->replaceForeach($tplFileContent);

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

        $offset = 0;
        while(preg_match('/<\/if>/', $content, $matches, PREG_OFFSET_CAPTURE, $offset)){
            $content = substr_replace($content, '<?php } ?>', $matches[0][1], mb_strlen($matches[0][0]));
            $offset = $matches[0][1] + mb_strlen($matches[0][0]);
        }
    }

    private function replaceForeach(&$content){
        $offset = 0;

        while(preg_match('/<foreach.*>/', $content, $matches, PREG_OFFSET_CAPTURE, $offset)){
            $attrMatches = [];
            preg_match_all('/(\w+)="(\w+)"/', $matches[0][0], $attrMatches);
            echo htmlspecialchars($matches[0][0]).'<br>';
            if($attrMatches[0]){
                $foreachParams = [];
                for($i = 0; $i < count($attrMatches[0]); $i++){
                    if($attrMatches[1][$i] == 'from'){
                        if(!isset($this->props[$attrMatches[2][$i]])){
                            throw new Exception("Property '{$attrMatches[2][$i]}' or value for it doesn't exist in this template");
                        }

                        $foreachParams['from'] = $attrMatches[2][$i];
                    }
                    if($attrMatches[1][$i] == 'value'){
                        $foreachParams['value'] = $attrMatches[2][$i];
                    }
                    if($attrMatches[1][$i] == 'key'){
                        $foreachParams['key'] = $attrMatches[2][$i];
                    }
                }

                if(!isset($foreachParams['from'])){
                    throw new Exception('Have not "from" in '.$matches[0][0]);
                }
                if(!isset($foreachParams['value'])){
                    throw new Exception('Have not "value" in '.$matches[0][0]);
                }

                if(isset($foreachParams['key'])){
                    $replacedForeach = 
                    '<?php foreach($template[\''.$foreachParams['from'].'\'] as $'.$foreachParams['key'].' => $'.$foreachParams['value'].'){ ?>';
                } else{
                    $replacedForeach = '<?php foreach($template[\''.$foreachParams['from'].'\'] as $'.$foreachParams['value'].'){ ?>';
                }
                
                $content = substr_replace($content, $replacedForeach, $matches[0][1], mb_strlen($matches[0][0]));
                $offset = $matches[0][1] + mb_strlen($replacedForeach);
            } else{
                throw new Exception('No params for '.$matches[0][0]);
                $offset = $matches[0][1] + mb_strlen($matches[0][0]);
            }

        }

        $offset = 0;
        while(preg_match('/<\/foreach>/', $content, $matches, PREG_OFFSET_CAPTURE, $offset)){
            $content = substr_replace($content, '<?php } ?>', $matches[0][1], mb_strlen($matches[0][0]));
            $offset = $matches[0][1] + mb_strlen('<?php } ?>');
        }
    }

    function writeMatches($r){
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/templates/matches.txt', $r);
    }
}