
<?php
error_reporting(0);
$servername = "127.0.0.1";
$username = "root";
$password = "mageiz";
$dbname = "bct";
$datatable = "bct_post"; // MySQL table name
$results_per_page = 25; // number of results per page

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<?php
if (isset($_GET["page"])) { $page  = $_GET["page"]; } else { $page=1; };
$start_from = ($page-1) * $results_per_page;
$sql = "SELECT * FROM ".$datatable." ORDER BY post_date DESC LIMIT $start_from, ".$results_per_page;
$rs_result = $conn->query($sql);
?>
<style>

.wrapper {
    max-width:auto;
}

.wrapper a {
    padding-right:10px;
}

.curPage {
    font-weight:bold;
    font-size:18px;
}
.pagination {
    font-size:16px;
}
</style>
<body>
<div class="wrapper">
<PRE>
<h2>The Lastest ANN in BCT</h2>
<table border="0" cellpadding="4">
<tr>
    <td bgcolor="#CCCCCC"><strong>No</strong></td>
    <td bgcolor="#CCCCCC"><strong>Title</strong></td><td bgcolor="#CCCCCC"><strong>Date</strong></td></tr>
<?php
$post_no = ($page-1)* $results_per_page +1;
 while($row = $rs_result->fetch_assoc()) {
?>
            <tr>
            <td><?php echo $post_no; ?></td>
            <td><a href= "<?php echo $row["post_url"]; ?>"  target="_blank" ><?php echo $row["post_title"]; ?></td>
            <td><?php echo $row["post_date"]; ?></td>
            </tr>
<?php
$post_no ++;
};
?>
</table>


<div class="pagination">
<?php
$sql = "SELECT COUNT(post_no) AS total FROM ".$datatable;
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$total_pages = ceil($row["total"] / $results_per_page); // calculate total pages with results

for ($i=1; $i<=$total_pages; $i++) {  // print links for all pages
            echo "<a href='index.php?page=".$i."'";
            if ($i==$page)  echo " class='curPage'";
            echo ">".$i."</a> ";
};
?></div>
 </div>
<p>
## [0.0.1] - 2018-06-10</br>
### Added</br>
- bitly link to analytics post views.</p>
</PRE>

</body>
</html>
