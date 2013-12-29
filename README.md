Chatsoul
========

A chat application powered by <b>Websync Comet Server</b> and MYSQL where users can create instant chatrooms without creating an account. See the project in action at http://chatsoul.com.


Things to remember before going live:

1. Secure a MYSQL database username and password.

2. Go to https://www.frozenmountain.com/signin.aspx and create an account.
   Once signed-up, you'll need to generate a "comet key" to use their comet server.

2. Create a folder and name it "configs" inside the root of the application.

3. Make sure the following folders exist on the root of the application and have proper read/write(775) permissions:<br />
   a.invite<br />
   b.configs<br />
   c.temporaryfiles<br />
   d.uploads
   
4. Configure the application by going to http://[nameofsite]/setup/configure.php.
   This will create the necessary database, tables and configurations to run the chat application.
   
   Feel free to fork the source code or email me at hunyoboy@gmail.com if you're interested to improve this project. Thanks. 
