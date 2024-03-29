<?php
    session_start();
    
    $link = mysqli_connect("shareddb-f.hosting.stackcp.net", "twitter-32363cba", "password", "twitter-32363cba");

    if (mysqli_connect_errno()){
        print(mysqli_connect_error());
        exit();
    }
    
    if($_GET['function'] == 'logout'){
        
        session_unset();
    }


    function time_since($since) {
    $chunks = array(
        array(60 * 60 * 24 * 365 , 'year'),
        array(60 * 60 * 24 * 30 , 'month'),
        array(60 * 60 * 24 * 7, 'week'),
        array(60 * 60 * 24 , 'day'),
        array(60 * 60 , 'hour'),
        array(60 , 'minute'),
        array(1 , 'second')
    );

    for ($i = 0, $j = count($chunks); $i < $j; $i++) {
        $seconds = $chunks[$i][0];
        $name = $chunks[$i][1];
        if (($count = floor($since / $seconds)) != 0) {
            break;
        }
    }

    $print = ($count == 1) ? '1 '.$name : "$count {$name}s";
    return $print;
    }
    
    function displayTweets($type){
        
        global $link;
        
        if($type == 'public'){
            
            $whereClause = "";
        } else if($type == 'isFollowing'){
            
            $query = "SELECT * FROM isFollowing WHERE follower = ". mysqli_real_escape_string($link, $_SESSION['id']);
            $result = mysqli_query($link, $query);
            
            $whereClause = "";
            
            while ($row = mysqli_fetch_assoc($result)) {
                
                if ($whereClause == ""){
                    $whereClause = "WHERE";
                }
                else {
                $whereClause.= " OR";
                }
                $whereClause.= " userid = ".$row['isFollowing'];
                
                
            }

        }  else if($type == 'yourtweets'){
            
            $whereClause = "WHERE `userid` = '".mysqli_real_escape_string($link, $_SESSION['id'])."'";
        }  else if($type == 'search'){
            
            echo "<p>Showing results for \"".mysqli_real_escape_string($link, $_GET['q'])."\"";
            
            $whereClause = "WHERE `tweet` LIKE '%".mysqli_real_escape_string($link, $_GET['q'])."%'";
        }else if(is_numeric($type)){
            
            
            $userQuery = "SELECT * FROM `Users` WHERE `id` = ".mysqli_real_escape_string($link, $type)." LIMIT 1";
$userQueryResult = mysqli_query($link, $userQuery);
            $user = mysqli_fetch_assoc($userQueryResult); 
            echo "<h2>".mysqli_real_escape_string($link, $user['email'])."'s Tweets</h2>";
            $whereClause = "WHERE `userid` =".mysqli_real_escape_string($link, $type);
        }
        
        $query = "SELECT * FROM `tweets` ".$whereClause." ORDER BY `datetime` DESC LIMIT 10";
        $result = mysqli_query($link, $query);
        
        if(mysqli_num_rows($result) == 0){
            
            echo "there are no tweets to display";
            
        }else{

            
            while($row = mysqli_fetch_assoc($result)){

                $userQuery = "SELECT * FROM `Users` WHERE `id` = ".mysqli_real_escape_string($link, $row['userid'])." LIMIT 1";
                $userQueryResult = mysqli_query($link, $userQuery);

                $user = mysqli_fetch_assoc($userQueryResult);
                echo "<div class='tweet'>";
                echo"<p><a href='?userid=".$user['id']."&page=publicprofiles'>".$user['email']."</a><span class='time' > ".time_since(time() - strtotime($row['datetime']))." ago</span></p>";
                
                echo "<p>".$row['tweet']."<br>"."</p>";
                echo "<p><a class='toggleFollow' data-userId=".$row['userid'].">";
                
                $isFollowingQuery = "SELECT * FROM `isFollowing` WHERE `follower` = " . mysqli_real_escape_string($link, $_SESSION['id'])." AND isFollowing=".mysqli_real_escape_string($link, $row['userid'])." LIMIT 1";
            $isFollowingQueryResult = mysqli_query($link, $isFollowingQuery);
  
                 if (mysqli_num_rows($isFollowingQueryResult) > 0){
                     echo "unfollow";
                 }else{
                     echo "follow";
                 }
                echo "</a></p></div>";
            }
            
        }
    }
    function displayUsers(){
        
        global $link;
        
        $query = "SELECT * FROM `Users` ORDER BY `id` DESC LIMIT 100";
        $result = mysqli_query($link, $query);
        
         while($row = mysqli_fetch_assoc($result)){


                echo "<div class='user'>";
                echo"<p><a href='?page=publicprofiles&userid= ".$row[id]."'>".$row['email']."</a> ";
                
                echo "<a class='toggleFollow' data-userId=".$row['id'].">";
                
                $isFollowingQuery = "SELECT * FROM `isFollowing` WHERE `follower` = " . mysqli_real_escape_string($link, $_SESSION['id'])." AND isFollowing=".mysqli_real_escape_string($link, $row['id'])." LIMIT 1";
            $isFollowingQueryResult = mysqli_query($link, $isFollowingQuery);
                 if (mysqli_num_rows($isFollowingQueryResult) > 0){
                     echo "unfollow";
                 }else{
                     echo "follow";
                 }
                echo "</a></p></div>";
            }
    }
    function displaySearch(){
        
        echo '<form class="form-inline">
              <div class="form-group mb-2">
                <input type="hidden" name="page" value="search">
                <input type="text" name="q" class="form-control" id="searchBox" placeholder="search...">
              </div>
              <button type="submit" class="btn btn-primary mb-2">Search</button>
            </form><hr>';
    }
    
    function displayTweetBox(){
        
        if($_SESSION['id'] > 0){
            echo '<div id="tweetSuccess" class="alert alert-success">Your tweet was posted successfully!</div><div id="tweetFail" class="alert alert-danger"></div>';            
            echo '<div class="form">
              <div class="form-group  mb-2">
                <textarea class="form-control" id="tweetContent" placeholder="tweet" rows="3"></textarea>
              </div>
              <button id="postTweet" type="input" class="btn btn-primary mb-2">Tweet</button>
            </div>';
        }
    }
?>