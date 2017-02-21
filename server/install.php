<?php
/*
* Copyright 2015 Litarvan & Vavaballz
*
* This file is part of OpenAuth.

* OpenAuth is free software: you can redistribute it and/or modify
* it under the terms of the GNU Lesser General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* OpenAuth is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU Lesser General Public License for more details.
*
* You should have received a copy of the GNU Lesser General Public License
* along with OpenAuth.  If not, see <http://www.gnu.org/licenses/>.
*/

// If the form was sent
if(isset($_POST))
	// If all the fields (except the password) are filled
	if(!empty($_POST['host']) && !empty($_POST['username']) && !empty($_POST['dbname']) && !empty($_POST['ownername']) && !empty($_POST['dbprefix'])) {
		// Trying to connect to the database
		try{
			$pdo = new PDO('mysql:dbname='. $_POST['dbname'] .';host='. $_POST['host'] .'', $_POST['username'], $_POST['password']);
			
			// Then setting failed to false
			$failed = false;
		} catch(PDOException $e) {
			// If it failed, setting failed to true
			$failed = true;

			// Setting the message to 'Unable to connect to the database !'
			$notif = ['type' => 'danger', 'msg' => 'Impossible de se connecter à la base de données !'];
		}

		// So if it didn't fail
		if(!$failed){
			// Checking if the database exists
			$exist = $pdo->prepare("SHOW TABLES LIKE 'cshop_users'");

			// If yes
		    if($exist->rowCount()==1) {
			$req = $pdo->prepare('
				ALTER TABLE '.$_POST['dbprefix'].' ADD COLUMN accessToken VARCHAR(255);
				ALTER TABLE '.$_POST['dbprefix'].' ADD COLUMN clientToken VARCHAR(255);
			');
			$req->execute();    
		    }else{
			$notif = ['type' => 'danger', 'msg' => 'Vous n\'avez pas une installation de CraftaShop valide !'];
		    }

		    // Getting the base config file
			$config_file = file('config_base.php');

			// Reading it
			foreach($config_file as $k=>$v)
				// Writing the owner
				if(strpos($v, "'owner' => ''"))
					$config_file[$k] = "\t\t'owner' => '{$_POST['ownername']}',\n";

				// Writing the database
				elseif(strpos($v, "'database' => ''"))
					$config_file[$k] = "\t\t'database' => '{$_POST['dbname']}',\n";

				// Writing the host
				elseif(strpos($v, "'host' =>"))
					$config_file[$k] = "\t\t'host' => '{$_POST['host']}',\n";

				// Writing the username
				elseif(strpos($v, "'username' =>"))
					$config_file[$k] = "\t\t'username' => '{$_POST['username']}',\n";

				// Writing the password
				elseif(strpos($v, "'password' =>"))
					$config_file[$k] = "\t\t'password' => '{$_POST['password']}',\n";
			        elseif(strpos($v, "'dbprefix' =>"))
					$config_file[$k] = "\t\t'dbprefix' => '{$_POST['dbprefix']}',\n";

			// Writing all int he config.php file
			file_put_contents('config.php', $config_file);

			// And refreshing the page
			echo "<meta http-equiv='refresh' content='0'>";
		}
	}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>OpenAuth Configuration</title>

        <!-- Bootstrap -->
        <link href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet">

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->

        <link href="http://Litarvan.github.io/OpenAuth-Server/style.css" rel="stylesheet">
    </head>

    <body>
        <div class="fulldiv classic">
            <img src="http://Litarvan.github.io/OpenAuth-Server/logo.png" />

            <h1>Configuration</h1>
            <br />
            <p class="marged-paragraph">
                Bienvenue dans la configuration de votre serveur OpenAuth.
                <br/>
                Nous allons maintenant configurer les identifiants de connexion à votre base Mysql.
                <br />
                Alors, qu'attendez vous ? !

                <br /><br /><br />
                
                <!-- Printing errors if any -->
				<p class="bg-<?php isset($notif) ? $notif['type'] : "warning" ?>"><?php isset($notif) ? $notif['msg'] : "" ?></p>

                <form method="post" action="">
                	<h2><u>Base de données</u></h2>
                	<br />

                    <label for="username">Hôte</label> : <input class="text-field" type="text" name="host" id="host" placeholder="Exemple: localhost" required/>
                    <br />
                    <label for="username">Nom l'utilisateur</label> : <input class="text-field" type="text" name="username" id="username" placeholder="Exemple: root"  required/>
                    <br />
                    <label for="password">Mot de Passe</label> : <input class="text-field" type="password" name="password" id="password"/>
                    <br />
                    <label for="redirecturl">Base de données</label> : <input class="text-field" type="text" name="dbname" id="dbname" placeholder="Exemple: openauth"  required/>
                    <br />
		    <label for="craftashopprefix">Préfix de vos tables (par défaut cshop_)</label> : <input class="text-field" type="text" name="dbprefix" id="dbprefix" placeholder="Exemple: cshop_"  required/>
                    <br />
                    <br />
                    
                	<h2><u>Infos du serveur</u></h2>
                	<br />

                    <label for="username">Owner</label> : <input class="text-field" type="text" name="ownername" id="ownername" placeholder="Votre nom" required/>
                    <br />
                    <br />
                    
                    <input class="submit-button" type="submit" value="Appliquer" />
                </form>
            </p>
        </div>

        <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
        <!-- Include all compiled plugins (below), or include individual files as needed -->
        <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
    </body>
</html>
