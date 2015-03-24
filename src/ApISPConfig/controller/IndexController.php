<?php
namespace ApISPConfig\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController {

    /**
     * Generates a string of random characters.
     *
     * @throws  LengthException  If $length is bigger than the available
     *                           character pool and $no_duplicate_chars is
     *                           enabled
     *
     * @param   integer $length             The length of the string to
     *                                      generate
     * @param   boolean $human_friendly     Whether or not to make the
     *                                      string human friendly by
     *                                      removing characters that can be
     *                                      confused with other characters (
     *                                      O and 0, l and 1, etc)
     * @param   boolean $include_symbols    Whether or not to include
     *                                      symbols in the string. Can not
     *                                      be enabled if $human_friendly is
     *                                      true
     * @param   boolean $no_duplicate_chars Whether or not to only use
     *                                      characters once in the string.
     * @return  string
     */
    private function random_string( $length=16, $human_friendly = FALSE, $include_symbols = TRUE, $no_duplicate_chars = FALSE )
    {
        $nice_chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefhjkmnprstuvwxyz23456789';
        $all_an     = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
        $symbols    = '!@#$%^&*()~_-=+{}[]|:;<>,.?/"\'\\`';
        $string     = '';

        // Determine the pool of available characters based on the given parameters
        if ( $human_friendly ) {
            $pool = $nice_chars;
        } else {
            $pool = $all_an;

            if ( $include_symbols ) {
                $pool .= $symbols;
            }
        }

        // Don't allow duplicate letters to be disabled if the length is
        // longer than the available characters
        if ( $no_duplicate_chars && strlen( $pool ) < $length ) {
            throw new \LengthException( '$length exceeds the size of the pool and $no_duplicate_chars is enabled' );
        }

        // Convert the pool of characters into an array of characters and
        // shuffle the array
        $pool = str_split( $pool );
        shuffle( $pool );

        // Generate our string
        for ( $i = 0; $i < $length; $i++ ) {
            if ( $no_duplicate_chars ) {
                $string .= array_shift( $pool );
            } else {
                $string .= $pool[0];
                shuffle( $pool );
            }
        }

        return $string;
    }

	private function stripAccents($string){
		$unwanted_array = array(
							'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y',
                            'Ğ'=>'G', 'İ'=>'I', 'Ş'=>'S', 'ğ'=>'g', 'ı'=>'i',
                            'ş'=>'s', 'ü'=>'u',
                            );
		$str =  strtr( $string, $unwanted_array );
		if(preg_match("/^[A-Za-z0-9\.\_-]*$/", $str)) {
			return $str;
		} else {
			return '';
		}
	}

    public function indexAction() {
    	$this->layout('default/simple');
    	$result = array();
    	return $result;
    }

    public function thanksAction() {
    	$this->layout('default/simple');
    	$result = array();
    	if ($_POST['accept'] and !empty($_POST['email']) and !empty($_POST['username'])) {
			$this->ispconfig();
		}
		$result['email'] = $_POST['email'];
    	return $result;
    }

    public function ispconfig() {
    	// include 'IspconfigClient.class.php';

		/*
		 * CONFIGURATION
		 */

		$port        = $app->config('ispconfig.port');
		$server      = $app->config('ispconfig.server');
		$reseller_id = $app->config('ispconfig.reseller_id');
		$location    = 'https://' . $server . ':' . $port . '/remote/index.php';
		$uri         = 'https://' . $server . ':' . $port . '/remote/';
		$login       = $app->config('ispconfig.login');
		$password    = $app->config('ispconfig.password');

		// /usr/local/ispconfig/interface/lib/classes/remoting.inc.php
		// https://github.com/cyberkathosting/ispconfig3/tree/master/remoting_client/examples
		$client = new \Ispconfig\Lib\IspconfigClient($location, $uri);

	    // $client = new SoapClient(null, array('location' => $location,'uri' => $uri));

		if (($session_id = $client->login($login, $password))!==false ) {

			if ($app->config('debug')) {
				echo 'Logged into remote server sucessfully. The SessionID is '.$session_id;
			}

	        /*
	        $client->sites_web_domain_add($session_id, $c);
	        $client->client_add($session_id, $reseller_id, $params);
	        */

	        // random username
	        // $username = 'test'.substr(time(),-5);

	        $username = $this->stripAccents(strtolower($_POST['username']));

	        if (empty($username)) {
	        	$this->flashMessenger()->addSuccessMessage( 'Le compte ' . $_POST['username'] . ' contient des caractères non supportés.' );
                    return $this->redirect()->toRoute('ispconfig', array(
                                'controller' => 'index',
                                'action'     => 'index'
                            ));
	        }

	        if (strlen($username)>50) {
	        	$this->flashMessenger()->addSuccessMessage( 'Le compte ' . $username . ' dépasse 50 caractères.' );
                    return $this->redirect()->toRoute('ispconfig', array(
                                'controller' => 'index',
                                'action'     => 'index'
                            ));
	        }

	        $password = $this->random_string();
	        $email    = $_POST['email'];
	        $time     = time();
	        $domain   = $username;

	        /*
	        if (strpos('.',$username)!==false) {
	            $domain = $username;
	        } else {
	            $domain = $username.'.milliweb.fr';
	        }
	        */

	        if ($client->sites_web_domain_get($session_id, $username)==null) {

	        	/*
	        	 * ADD CLIENT
	        	 *
	        	 */ 
	            $params                        = array();
	            $params['contact_name']        = $username;
	            $params['country']             = 'FR';
	            $params['username']            = $username;
	            $params['password']            = $password;
	            $params['email']               = $email;
	            $params['usertheme']           = 'default';
	            $params['web_php_options']     = 'fast-cgi';
	            // $params['ssh_chroot']          = 'jailkit';
	            $params['limit_web_quota']     = '1000';
	            $params['limit_database']      = '1';
	            $params['limit_database_user'] = '1';
	            $params['limit_web_domain']    = '1';
	            $params['limit_ftp_user']      = '1';
	            // if($client['force_suexec'])
	            // $params['force_suexec']        = 'y';
	            $params['limit_shell_user']    = '1';
	            // $params['ssh_chroot']          = 'jailkit';
	            $params['default_webserver']   = '1';

	            $sys_userid = $client->client_add($session_id, $reseller_id, $params);

	            /*
	        	 * ADD DOMAIN
	        	 *
	        	 */
	            $params                      = array();
	            $params['sys_userid']        = $sys_userid;
	            $params['sys_groupid']       = $sys_userid;
	            $params['hd_quota']          = 1000;
	            $params['traffic_quota']     = '-1';
	            $params['server_id']         = 1;
	            $params['suexec']            = 'y';
	            $params['ip_address']        = '*';
	            $params['type']              = 'vhost';
	            $params['vhost_type']        = 'name';
	            $params['active']            = 'y';
	            $params['php']               = 'fast-cgi';
	            $params['domain']            = $domain;
	            $params['pm_max_requests']   = 0;
	            $params['allow_override']    = 'All';
	            $params['pm_process_idle_timeout'] = 10;

	            $domain_id = $client->sites_web_domain_add($session_id, $sys_userid, $params);

	            /*
	        	 * ADD DATABASE USER
	        	 *
	        	 */
	            $db_username = 'c'.$sys_userid.'db_user';
	            $params                      = array();
	            $params['sys_userid']        = $sys_userid;
	            $params['sys_groupid']       = $sys_userid;
	            $params['database_user']     = $db_username;
	            $params['database_password'] = $password;

	            $database_user = $client->sites_database_user_add($session_id, $sys_userid, $params);

	            /*
	        	 * ADD DATABASE
	        	 *
	        	 */
	            $db_name = str_replace('.', '_', $username);
	            $params                      = array();
	            $params['sys_userid']        = $sys_userid;
	            $params['sys_groupid']       = $sys_userid;
	            $params['database_user_id']  = $database_user;
	            $params['database_name']     = $db_name;
	            $params['parent_domain_id']  = $domain_id;
	            $params['active']            = 'y';
	            $params['server_id']         = 1;
	            $params['type']              = 'mysql';
	            $params['remote_access']     = 'n';
	            $params['remote_ips']        = '37.187.138.220';

	            $client->sites_database_add($session_id, $sys_userid, $params);

	            /*
	        	 * ADD MAIL USER
	        	 *
	        	 */
				/*
	            $params = array(
	                        'server_id' => 1,
	                        'email'     => $username . '@appydo.com',
	                        'login'     => $username . '@appydo.com',
	                        'password'  => $password,
	                        'name'      => $username,
	                        // 'uid'       => $sys_userid,
	                        // 'gid'       => $sys_userid,
	                        // 'maildir'   => '/var/vmail/test.int/joe',
	                        'quota'     => 104857600,
	                        'cc'        => '',
	                        'homedir'   => '/var/vmail',
	                        'autoresponder' => 'n',
	                        'autoresponder_start_date' => array('day' => 1,'month' => 7, 'year' => 2012, 'hour' => 0, 'minute' => 0),
	                        'autoresponder_end_date'   => array('day' => 20,'month' => 7, 'year' => 2012, 'hour' => 0, 'minute' => 0),
	                        'autoresponder_text'       => '',
	                        'move_junk' => 'n',
	                        'custom_mailfilter' => 'spam',
	                        'postfix'        => 'y',
	                        'access'         => 'n',
	                        'disableimap'    => 'n',
	                        'disablepop3'    => 'n',
	                        'disabledeliver' => 'n',
	                        'disablesmtp'    => 'n'
	                        );
	            $client->mail_user_add($session_id, $sys_userid, $params);
				*/

				/*
	        	 * ADD FTP USER
	        	 *
	        	 */
	            /*
	            $params                      = array();
	            $params['sys_userid']        = $sys_userid;
	            $params['sys_groupid']       = $sys_userid;
	            $params['server_id']         = 1;
	            $params['username']          = $username;
	            $params['password']          = $password;
	            $params['active']            = 'y';
	            $params['parent_domain_id']  = $domain_id;
	            $params['quota_size']        = -1;
	            $params['gid']               = 'client'.$sys_userid;
	            $params['uid']               = 'web'.$domain_id;
	            $params['dir']               = '/var/www/clients/client'.$sys_userid.'/web'.$domain_id.'/web';

	            $client->sites_ftp_user_add($session_id, $sys_userid, $params);
	            */

	            /*
	        	 * ADD SHELL USER
	        	 *
	        	 */
	            $params                      = array();
	            $params['server_id']         = 1;
	            $params['parent_domain_id']  = $domain_id;
	            $params['sys_userid']        = $sys_userid;
	            $params['sys_groupid']       = $sys_userid;
	            $params['quota_size']        = 1000;
	            $params['username_prefix']   = $username;
	            $params['username']          = $username.'ssh';
	            $params['password']          = $password;
	            $params['active']            = 'y';
	            $params['puser']             = 'web'.$domain_id;
	            $params['pgroup']            = 'client'.$sys_userid;
	            $params['shell']             = '/bin/bash';
	            $params['ssh_rsa']           = 'ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQCon/EMKcDloR35QscqUgjbEZojRXq/zLzMNeH1AltIMxGQcQF7All1ncKCatsOqKXFI4+g1W3nH79hZh307vbAI340nVwutGj92q9HpA5+QXwP/b8I7HLOSQvZclv71RMimBKlnNEJqBLG9q/fiHXZOcGzVT3DCmzqLStpW/hv7wg5MVsZzBbiO75UFASLwU/8rCVASPXORuQLphcZp7+zwmLZMwwpa7X4uRhq/vbPsqtlg27rMS6r7xKOVcQwTlXlVSZH+sIw8GwXay9u1eVPY7T62J0XEzaTlGGxtylilvbYfSC7y72ffEMFgiA5WZHc75+rrov6KQHmo0/WKjA7
ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQDucBVnXxGLNgI/5MnISxbeasNkr9DbsheTsU1hIO+4JzHRTV78HR9PeCGkO46VP4j7LphEwQeTHwyz1yO+vCRB6J/fugd0sQHmnx29qlGq0QClTD1HnuV65580Kwp+UxF7cCWpqF8jIOyyOaQhGZyoxj6rQG+S592XO+6pgzy4+zyYJLrLKCdHMpBg8HsQ5rQQW87cPnn+3sjxsuDGXzceimb3+9IErSeaimEnT3WZ64Z2b99D0Iv/2cMET5hBn2HqRDpfSkoaARG8imc5sEQQ8bVCSIFDsUBi0dVVvbdN0YgPsY/zLXXNl5mk56V+IGU36p23wRF0hjT6HHpqhRAD';
	            // $params['dir']               = '';
	            $params['chroot']            = 'jailkit';
	            $params['dir']               = '/var/www/clients/client'.$sys_userid.'/web'.$domain_id;
	            $client->sites_shell_user_add($session_id, $sys_userid, $params);

	            $content = "
	            Bonjour,

	            Merci d'avoir créer un compte sur milliweb.fr. Il sera actif dans quelques minutes.

	            Voici tous vos identifiants pour gérer votre espace web :

	            Accès site web
	            http://{$server}/{$domain}

	            Accès interface administration
	            Url       : https://{$server}:8080/
	            Login     : {$username}
	            Password  : {$password}

	            Gestion Base de donnée
	            Interface : https://{$server}/phpmyadmin
	            Base      : {$db_name}
	            Login     : {$db_username}
	            Password  : {$password}

	            Configuration Email
	            Interface : https://{$server}/webmail
	            Login     : {$username}
	            Password  : {$password}

	            Configuration SSH
	            Serveur   : {$server}
	            Login     : {$username}ssh
	            Password  : {$password}
	            ssh -p 208 {$username}ssh@ks3266303.kimsufi.com

	            Configuration FTP
	            Serveur   : {$server}
	            Login     : {$username}
	            Password  : {$password}

	            Configuration DNS
	            Serveur primaire   : {$server}
	            Serveur secondaire : sdns2.ovh.net

	            Merci de vous connecter à votre espace dans les 24h pour activer votre compte.

	            Cordialement,

	            Support Client Milliweb
	            Email : support@milliweb.fr
	            http://www.milliweb.fr
	            ";

	            mail($email, $app->config('email.subject'), $content);

	            // test loop
	            /*
	            $c=1;
	            while($domain_record = $client->sites_web_domain_get($session_id, $c)) {
                    var_dump($domain_record);
                    $c++;
	            }
	            $c=1;
	            while($domain_record = $client->client_get($session_id, $c)) {
                    var_dump($domain_record);
                    $c++;
	            }
	            */

	        } else {
	            echo "The domain name ".$domain." is already use, thanks to use another one.";
	        }

	        $client->logout($session_id);
	    }
	    return array('email'=>$email);
    }

}

