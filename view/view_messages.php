<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title><?= $recipient->pseudo ?>'s Messages</title>
        <base href="<?= $web_root ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="css/styles.css" rel="stylesheet" type="text/css"/>
        <script src="lib/jquery-3.6.0.min.js" type="text/javascript"></script>
        <script>
            let messages = <?= $messages_json ?>;
            let tblMessages;
            let sortColumn = 'datetime';
            let sortAscending = false;
            let postButton;
            const recipient = "<?= $recipient->pseudo ?>";
            const user = "<?= $user->pseudo ?>";
            
            
            $(function(){
                //le bouton "post" est désactivé par défaut.
                //il s'active quand le champs "body" possède au moins un caractère 
                $('#post').attr("disabled", true);
                $("#body").on("input", function () {
                    $('#post').attr("disabled", $(this).val().length === 0);
                });
                
                //le formulaire est caché par défaut.
                //quand on clique sur le titre, il s'affiche ou se cache.
                $("#message_form").hide();
                $("#enableMessageForm").click(function(){
                    $("#message_form").toggle("fast", function () {
                        if($("#message_form").is(":visible")){
                            $("#enableMessageForm").html("Click here to hide the new message form.");
                            $("#body").focus();
                        } else {
                            $("#enableMessageForm").html("Click here to leave a message.");
                        }
                    });
                });
                
                tblMessages = $('#message_list');
                tblMessages.html("<tr><td>Loading...</td></tr>");
                getMessages();
                
                $("#refresh").removeAttr("hidden");
                $("#refresh").click(function(){
                    getMessages();
                });
                
                postButton = $('#post');
                postButton.attr("type", "button");
                postButton.click(postMessage);
            });
            
            function getMessages() {
                // éviter de mettre ce message à chaque fois, sinon ça donne un effet moins fluide
                //tblMessages.html("<tr><td>Loading...</td></tr>");
                
                $.get("message/get_visible_messages_service/"+recipient, function(data){
                    messages = data;
                    sortMessages();
                    displayTable();
                }, "json").fail(function(){
                    tblMessages.html("<tr><td>Error encountered while retrieving the messages!</td></tr>");
                });
                        
            }
            
            function postMessage(){
                const newMsg = {id: -1,
                              datetime: 'creating...',
                              body: $("#body").val(),
                              author: user,
                              erasable: false,
                              private: ($("#private").is(':checked')) ? '1' : '0'
                             };
                messages.push(newMsg);
                sortMessages();
                displayTable();
                
                const data = {body: $("#body").val()};
                if ($("#private").is(':checked')) {
                    data.private = "1";
                }
                
                $.post("message/add_service/" + recipient,
                    data,
                    function (data) {
                        getMessages();
                    }
                ).fail(function(){
                    alert("<tr><td>Error encountered while retrieving the messages!</td></tr>");
                    getMessages();
                });
            }
            
            function deleteMessage(id){
                const idx = messages.findIndex(function (el, idx, arr) {
                    return el.id === id;
                });
                messages.splice(idx, 1);
                displayTable();
                
                $.post("message/delete_service/" + recipient,
                    {"id_message": id}
                ).fail(function(){
                    alert("<tr><td>Error encountered while retrieving the messages!</td></tr>");
                    getMessages();
                });
            
            }
  
            function sortMessages() {
                messages.sort(function (a,b) {
                    if (a[sortColumn] < b[sortColumn])
                        return sortAscending ? -1 : 1;
                    if (a[sortColumn] > b[sortColumn])
                        return sortAscending ? 1 : -1;
                    return 0;
                });
            }

            function sort(field) {
                if (field === sortColumn)
                    sortAscending = !sortAscending;
                else {
                    sortColumn = field;
                    sortAscending = true;
                }
                sortMessages();
                displayTable();
            }
            
            function displayTable(){
                let html = "<tr><th id='col_datetime' onclick='sort(\"datetime\");'>Date/Time</th>" +
                           "<th id='col_author' onclick='sort(\"author\");'>Author</th>" + 
                           "<th id='col_body' onclick='sort(\"body\")';>Message</th>" + 
                           "<th>Private?</th>" + 
                           "<th>Action</th></tr>";
                for (let m of messages) {
                    html += "<tr>";
                    html += "<td>" + m.datetime + "</td>";
                    html += "<td><a href='member/profile/"+m.author+"'>"+m.author+"</a></td>";
                    html += "<td>" + m.body + "</td>";
                    html += "<td><input type='checkbox' disabled" + (m.private === '1' ? ' checked' : '') + "></td>";
                    html += "<td>" + (m.erasable ? "<a href='javascript:deleteMessage(\"" + m.id + "\")'>erase</a>" : "") + "</td>";
                    html += "</tr>";
                }
                tblMessages.html(html);
                $('#col_' + sortColumn).append(sortAscending ? ' &#9650;' : ' &#9660;');
            }
            
            
            
            
        
        </script>
    </head>
    <body>
        <div class="title"><?= $recipient->pseudo ?>'s Messages</div>
        <?php include('menu.html'); ?>
        <div class="main">
            <div id="enableMessageForm">Click here to leave a message.</div>
            <form id="message_form" action="message/index/<?= $recipient->pseudo ?>" method="post">
                <textarea id="body" name="body" rows='3'></textarea><br>
                <input id="private" name="private" type="checkbox">Private message<br>
                <input id="post" type="submit" value="Post">
            </form>
            
            <?php if (count($errors) != 0): ?>
                <div class='errors'>
                    <p>Please correct the following error(s) :</p>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            
            <p>These are <?= $recipient->pseudo ?>'s messages:</p>

            <input id="refresh" type="button" value="Refresh" hidden>

            <table id="message_list" class="message_list">
                <tr>
                    <th>Date/Time</th>
                    <th>Author</th>
                    <th>Message</th>
                    <th>Private?</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($messages as $message): ?>
                    <?php if (($message->private && ($message->author == $user || $message->recipient == $user)) || !$message->private): ?>
                        <tr>
                            <td><?= $message->date_time ?></td>
                            <td><a href='member/profile/<?= $message->author->pseudo ?>'><?= $message->author->pseudo ?></a></td>
                            <td><?= $message->body ?></td>
                            <td><input type='checkbox' disabled <?= ($message->private ? ' checked' : '') ?>></td>
                            <td>
                                <?php if ($user == $message->author || $user == $message->recipient): ?>
                                    <form class='link' action='message/delete' method='post' >
                                        <input type='text' name='id_message' value='<?= $message->post_id ?>' hidden>
                                        <input type='submit' value='erase'>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </table>
        </div>
    </body>
</html>