
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>English IL</title>
</head>
<body>
<div style="width:750px;">
    <h3 class="item-title">{{$testdescription}}</h3>
    <div id="learnosity_assess"></div>


</div>

<script src="//items.learnosity.com/"></script>
<script>
    var itemsApp = LearnosityItems.init(<?php echo $signedRequest; ?>);
</script>
</body>