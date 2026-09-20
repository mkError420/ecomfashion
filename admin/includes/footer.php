    </div><!-- /.content -->
  </div><!-- /.main -->
</div><!-- /.admin-layout -->
<script>
(function(){
  var t=document.getElementById('menuToggle'),s=document.getElementById('sidebar'),b=document.getElementById('backdrop');
  function close(){s.classList.remove('open');b.classList.remove('show');}
  if(t){t.addEventListener('click',function(){s.classList.toggle('open');b.classList.toggle('show');});}
  if(b){b.addEventListener('click',close);}
})();
</script>
</body>
</html>
