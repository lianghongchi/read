<?php

class lib_util_docparser
{



    private $params = [];



    public function parse($doc = '')
    {
        if ($doc == '') {
            return $this->params;
        }
        if (preg_match ('#^/\*\*(.*)\*/#s', $doc, $comment) === false) {
            return $this->params;
        }

        $comment = trim ($comment[1]);
        if (preg_match_all ( '#^\s*\*(.*)#m', $comment, $lines ) === false) {
            return $this->params;
        }

        $this->parseLines($lines[1]);
        $params = $this->params;
        $this->params = [];

        return $params;
    }


    private function parseLines($lines)
    {
        foreach ($lines as $key => $line) {
            $parsedLine = $this->parseLine($line);

            if ($parsedLine === false && ! isset ($this->params['description'])) {
                if (isset ($desc)) {
                    $this->params['description'] = implode(PHP_EOL, $desc);
                }
                $desc = [];
            } elseif ($parsedLine !== false) {
                $desc [] = $parsedLine;
            }
        }
        $desc = implode(' ', $desc);
        if ($desc) {
            $this->params['long_description'] = $desc;
        }
    }


    private function parseLine($line)
    {
        $line = trim ($line);

        if (!$line) {
            return false;
        }

        if (strpos($line, '@') === 0) {
            if (strpos($line, ' ') > 0) {
                $param = substr($line, 1, strpos($line, ' ') - 1 );
                $value = substr($line, strlen($param) + 2);
            } else {
                $param = substr($line, 1 );
                $value = '';
            }
            if ($this->setParam ( $param, $value )) {
                return false;
            }
        }

        return $line;
    }


    private function setParam($param, $value)
    {
        if ($param == 'param' || $param == 'return') {
            $value = $this->formatParamOrReturn ( $value );
        }

        if ($param == 'class')
            list ( $param, $value ) = $this->formatClass ( $value );

        if (empty ( $this->params [$param] )) {
            $this->params [$param] = $value;
        } else if ($param == 'param') {
            $arr = array (
                    $this->params [$param],
                    $value
            );
            $this->params[$param] = $arr;
        } else {
            $this->params[$param] = $value + $this->params [$param];
        }

        return true;
    }

    private function formatClass($value)
    {
        $r = preg_split ( "[\(|\)]", $value );
        if (is_array ( $r )) {
            $param = $r [0];
            parse_str ( $r [1], $value );
            foreach ( $value as $key => $val ) {
                $val = explode ( ',', $val );
                if (count ( $val ) > 1)
                    $value [$key] = $val;
            }
        } else {
            $param = 'Unknown';
        }
        return array (
                $param,
                $value
        );
    }

    private function formatParamOrReturn($string)
    {
        $pos = strpos($string, ' ');

        $type = substr ($string, 0, $pos);
        return '(' . $type . ')' . substr ($string, $pos + 1);
    }


}
