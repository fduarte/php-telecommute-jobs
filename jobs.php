<?php 
ini_set ('display_errors', 1); 
ini_set ('display_startup_errors', 1); 
error_reporting(E_ALL | E_STRICT); 
include('simplehtmldom/simple_html_dom.php');

/**
 * Telecommuting Jobs Scrapper
 * 2012-02-22
 * Author: fredduarte@gmail.com
 */
class Job extends simple_html_dom_node{

    const logger = "errors.log";
    private $ycombinator = array('name'=>'Y Combinator','url'=>'http://news.ycombinator.com/jobs');
    private $craigslist = array('name'=>'Craigslist (telecommuting)', 'url'=>'https://www.google.com/search?q=%22This+is+a+contract+job%22+%22Telecommuting+is+ok.%22+PHP+site:craigslist.org&bav=on.2,or.r_gc.r_pw.,cf.osb&hl=en');
    private $signals = array('name'=>'37signals (telecommuting)', 'url'=>'http://jobs.37signals.com/jobs/search?term=anywhere');
    private $signals2 = array('name'=>'37signals (telecommuting)', 'url'=>'https://www.google.com/#sclient=psy-ab&hl=en&site=&source=hp&q=%22PHP%22%20%22anywhere%22%20site%3Ahttp%3A%2F%2Fjobs.37signals.com%2Fjobs&pbx=1&oq=&aq=&aqi=&aql=&gs_sm=&gs_upl=&bav=on.2,or.r_gc.r_pw.,cf.osb&fp=9c4f9ba461693a79&biw=1170&bih=595&pf=p&pdl=3000');
    private $stackOverflow = array('name'=>'Stack Overflow', 'url'=>'http://careers.stackoverflow.com/jobs?searchTerm=php&range=20&istelecommute=true');

    public function __construct()
    {
        echo '<p>' . date('Y-m-d', time()) . '</p>';
        
        // ycombinator
        $ycombinatorLinks = $this->ycombinatorParser($this->ycombinator['url']);
        $this->write($this->ycombinator['name'], $ycombinatorLinks);

        // craiglist
        $craigslistLinks = $this->craigslistParser($this->craigslist['url']);
        $this->write($this->craigslist['name'], $craigslistLinks);

        // 37 signals
        $signalsLinks = $this->signalsParser($this->signals['url']);
        $this->write($this->signals['name'], $signalsLinks);

        /*
        // stack overflow
        $stackOverflowLinks = $this->stackOverflowParser($this->stackOverflow['url']);
        $this->write($this->stackOverflow['name'], $stackOverflowLinks);
         */
    }

    private function stackOverflowParser($url)
    {
        try {
            $html = file_get_html($url); 

            foreach($html->find('div.joblist a.title') as $e) {
                echo $e->plaintext . '<br>';
                //$links[] = array($title->plaintext, 'http://jobs.37signals.com'.$e->href);
                #$links[$title->plaintext] = 'http://jobs.37signals.com'.$e->href;
            }

            $html->clear();
            unset($html);
        } catch (Exception $e) {
            $this->log($e);
            var_dump('<pre>'.$e->getMessage().'</pre>');
        }

        return $links;
    }

    private function signalsParser($url)
    {
        try {
            $html = file_get_html($url); 

            foreach($html->find('div#job_list a') as $e) {
                // if title is empty it's an unwanted link
                $title =  $e->find('span.title', 0);
                if (!empty($title)) {
                    //$links[] = array($title->plaintext, 'http://jobs.37signals.com'.$e->href);
                    $links[$title->plaintext] = 'http://jobs.37signals.com'.$e->href;
                }
            }

            $html->clear();
            unset($html);
        } catch (Exception $e) {
            $this->log($e);
            var_dump('<pre>'.$e->getMessage().'</pre>');
        }

        return $links;
    }

    private function craigslistParser($url)
    {
        try {
            $html = file_get_html($url); 

            foreach($html->find('div#ires a') as $e) {
                if ($e->plaintext !== 'Similar') {
                    $href = explode('q=', $e->href);
                    $href2 = explode('&', $href[1]);
                    $links[$e->plaintext] = $href2[0];
                }
            }

            $html->clear();
            unset($html);
        } catch (Exception $e) {
            $this->log($e);
            var_dump('<pre>'.$e->getMessage().'</pre>');
        }

        return $links;
    }

    private function ycombinatorParser($url)
    {
        try {
            $html = file_get_html($url); 
            $links = array();

            foreach($html->find('td.title a') as $e) {
                if (strstr($e->href, 'http')) {
                    $links[$e->plaintext]  = $e->href;
                } else {
                    $links[$e->plaintext] = 'http://news.ycombinator.com/' . $e->href;
                } 
            }

            $html->clear();
            unset($html);
        } catch (Exception $e) {
            $this->log($e);
            var_dump('<pre>'.$e->getMessage().'</pre>');
        }

        $links = !empty($links) ? $links : null; 
        return $links;
    }

    private function write($title, $links)
    {
        if (empty($links)) {
            return false;
        }

        try {
            echo '<h3>' . $title . '</h3><ul>';
            foreach ($links as $title=>$url) {
                echo '<li><a href="' . $url . '">' . $title . '<a></li>';
            } 
            echo '</ul>';
        } catch (Exception $e) {
            $this->log($e);
        }
    }

    private function log($exception) 
    {
        $data = $exception->getFile() . '\n\n' . $exception->getLine() . '\n\n' . $exception->getMessage() . '\n\n';
        error_log('Exception:\n\n' . $data, 3, self::logger);
    }

}

$job = new Job();
