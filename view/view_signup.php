<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Sign Up</title>
        <base href="<?= $web_root ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="css/styles.css" rel="stylesheet" type="text/css"/>
        <script src="lib/jquery-3.6.0.min.js" type="text/javascript"></script>
        <script>
            let pseudo, errPseudo, password, errPassword, passwordConfirm, errPasswordConfirm;
            
            $(function(){
                    pseudo = $("#pseudo");
                    errPseudo = $("#errPseudo");
                    password = $("#password");
                    errPassword = $("#errPassword");
                    passwordConfirm = $("#passwordConfirm");
                    errPasswordConfirm = $("#errPasswordConfirm");
                    
                    $("input:text:first").focus();
                }
            );
            
            function checkPseudoExists(){
                $.get("member/user_exists_service/"+pseudo.val(),
                      function(data){
                          if(data === "true"){
                              errPseudo.append("<p>Pseudo already exists.</p>");
                          }
                      }
                );
            }

            function checkPseudo(){
                let ok = true;
                errPseudo.html("");
                if(!(/^.{3,16}$/).test(pseudo.val())){
                    errPseudo.append("<p>Pseudo length must be between 3 and 16.</p>");
                    ok = false;
                }
                if(pseudo.val().length > 0 && !(/^[a-zA-Z][a-zA-Z0-9]*$/).test(pseudo.val())){
                    errPseudo.append("<p>Pseudo must start by a letter and must contain only letters and numbers.</p>");  
                    ok = false;
                }
                return ok;
            }
            
            function checkPassword(){
                let ok = true;
                errPassword.html("");
                const hasUpperCase = /[A-Z]/.test(password.val());
                const hasNumbers = /\d/.test(password.val());
                const hasPunct = /['";:,.\/?\\-]/.test(password.val());
                if(!(hasUpperCase && hasNumbers && hasPunct)){
                    errPassword.append("<p>Password must contain one uppercase letter, one number and one punctuation mark.</p>");
                    ok = false;
                }
                if(!(/^.{8,16}$/).test(password.val())){
                    errPassword.append("<p>Password length must be between 8 and 16.</p>");
                    ok = false;
                }
                return ok;
            }
            
            function checkPasswords(){
                let ok = true;
                errPasswordConfirm.html("");
                if(password.val() !== passwordConfirm.val()){
                    errPasswordConfirm.append("<p>You have to enter twice the same password.</p>");
                    ok = false;
                }
                return ok;
            }
            
            function checkAll(){
                // les 3 lignes ci-dessous permettent d'éviter le shortcut
                // par rapport à checkPseudo()&&checkPassword()&&checkPasswords();
                let ok = checkPseudo();
                ok = checkPassword() && ok;
                ok = checkPasswords() && ok;
                return ok;
            }   
        
        </script>
    </head>
    <body>
        <div class="title">Sign Up</div>
        <div class="menu">
            <a href="index.php">Home</a>
        </div>
        <div class="main">
            Please enter your details to sign up :
            <br><br>
            <form id="signupForm" action="main/signup" method="post" onsubmit="return checkAll();">
                <table>
                    <tr>
                        <td>Pseudo:</td>
                        <td><input id="pseudo" name="pseudo" type="text" size="16" value="<?= $pseudo ?>" oninput="checkPseudo();" onchange="checkPseudoExists();"></td>
                        <td class="errors" id="errPseudo"></td>
                    </tr>
                    <tr>
                        <td>Password:</td>
                        <td><input id="password" name="password" type="password" size="16" value="<?= $password ?>" oninput="checkPassword();"></td>
                        <td class="errors" id="errPassword"></td>
                    </tr>
                    <tr>
                        <td>Confirm Password:</td>
                        <td><input id="passwordConfirm" name="password_confirm" size="16" type="password" value="<?= $password_confirm ?>" oninput="checkPasswords();"></td>
                        <td class="errors" id="errPasswordConfirm"></td>
                    </tr>
                </table>
                <input id="btn" type="submit" value="Sign Up">
            </form>
            <?php if (count($errors) != 0): ?>
                <div class='errors'>
                    <br><br><p>Please correct the following error(s) :</p>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </body>
</html>