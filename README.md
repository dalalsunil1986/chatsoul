Chatsoul
========

A chat application powered by Websync comet server where users can create instant chatrooms without creating an account.
Check the project in action at http://chatsoul.com.


Things to remember before going live:

1.Secure a database username and password.

2.Create a folder and name it "configs" inside the root of the application.

3.Make sure the following folders exist on the root of the application and have proper read/write(775) permissions:
   
   a.invite<br />
   b.configs<br />
   c.temporaryfiles<br />
   d.uploads
   
4.Configure the application by going to http://[nameofsite]/setup/configure.php.
  This will create the necessary database, tables and configurations for the application.
