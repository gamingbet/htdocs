</div>
    <script>
		var width = $(".menu-glowne").width();
		var nowa = $( window ).width() - width;
		var nowa2 = nowa - 200;
		var nowa3 = nowa - $(".right-menu").width()-20;
		var wydarzenie = nowa - 420;
		$(document).ready(function(){
			$(".center").width(nowa);
			$(".gry-linki").width(nowa2);
			$(".center-content").width(nowa3);
			$(".wydarzenie").width(wydarzenie);
			$(window).resize(function(){
				var news = $('.center-news-33');
				news.height(news.width());
			}).trigger('resize');
			
		});	
    </script>   
    <script src="js/classie.js"></script>
	<script>
    $(function(){
        var windowH = $(window).height();
        $('.menu-glowne').css({'height':($(window).height())-80+'px'});
        $('.center').css({'height':($(window).height())-80+'px'});                                                                                   
    });
    </script>
	<script>
		var 
			menuRight = document.getElementById( 'cbp-spmenu-s2' ),
			showRightPush = document.getElementById( 'showRightPush' ),
			body = document.body;

			showRightPush.onclick = function() {
				classie.toggle( this, 'active' );
				classie.toggle( body, 'cbp-spmenu-push-toleft' );
				classie.toggle( menuRight, 'cbp-spmenu-open' );
				};
	</script>



<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-41050639-1', 'gamingbet.eu');
  ga('send', 'pageview');

</script>

</body>

</html>

