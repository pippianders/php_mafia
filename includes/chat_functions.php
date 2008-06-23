<?php

   function add_message($message_text, $user_id, $game_id) {
       $message_text = mysqli_real_escape_string($message_text);
       $query = "INSERT INTO channel_message(game_id, user_id, message_text, message_date) ".
                "VALUES('$game_id', '$user_id', '$message_text', NOW())";
       $result = mysqli_query($dbh, $query);
       if($result && mysqli_affected_rows($dbh) == 1) {
           //successful
       }
   }

   function retrieve_new_messages($user_id, $game_id, $id = 0) {
       global $dbh;
       $channels = array();
       $query = "SELECT channel_members.channel_id ".
                "FROM channel_members, channels ".
                "WHERE channel_members.user_id='$user_id' AND channels.game_id='$game_id' AND ".
                "channel_members.channel_id=channels.channel_id";
       $result = mysqli_query($dbh, $query);
       if($result && mysqli_num_rows($result) > 0) {
           while($row = mysqli_fetch_array($result)) {
               $channels[] = $row['channel_id'];
           }
       } else {
           echo "$query";
       }
       if(count($channels) > 0) {
           $query = "SELECT users.user_name, channel_messages.message_id, ".
                    "channel_messages.message_text, ".
                    "DATE_FORMAT(channel_messages.message_date, '%H:%m:%i') as message_date ".
                    "FROM channel_messages, users ".
                    "WHERE users.user_id=channel_messages.user_id AND channel_messages.message_id > '$id' ";
           $channel_query = " AND (";
           foreach($channels as $channel) {
               if($channel_query != " AND (") {
                   $channel_query .= " OR ";
               }
               $channel_query .= "channel_messages.channel_id='$channel'";
           }
           $channel_query .= ")";
           $query = $query . $channel_query . " ORDER BY channel_messages.message_date";
           $result = mysqli_query($dbh, $query);
           if($result) {
               $xml = "<?xml version='1.0' encoding='UTF-8'?>\n";
               $xml .= "<messages>\n";
               if(mysqli_num_rows($result) > 0) {
                   while($row = mysqli_fetch_array($result)) {
                       $message_text = $row['message_text'];
                       $message_date = $row['message_date'];
                       $message_id = $row['message_id'];
                       $user_name = $row['user_name'];
                       $xml .= "<message>\n";
                       $xml .= "<id>$message_id</id>\n";
                       $xml .= "<user>$user_name</user>\n";
                       $xml .= "<date>$message_date</date>\n";
                       $xml .= "<text>$message_text</text>\n";
                       $xml .= "</message>\n";
                   }
               }
               $xml .= "</messages>\n";
               return "$xml";
           }
       }
   }


?>
