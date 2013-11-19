// project: tableSearch
// author: Soone 
// date: 190113
//
// description: Search into a table and display results taht match the query
// jquery version:1.8
//
jQuery(function(){
 
    $.fn.tablesearch = function(args){
      args = $.extend({
        color: "black",
        bgcolor: "yellow"
      }, args);
      // place your code here

       var tables = this;

       //on submit 
       $("#formTS").on("submit",function(){

          //delete yellow hightlight
          $(".yellowTS").each(function(){
              var tx = $(this).text();
              $(this).replaceWith(tx);
          });

          //get query
          var query = $("#queryTS").val();
          //for each tables
         tables.each(function(){
            var td = $(this).find('td');
            td.parent().hide(); //hide all table line tr
            //for each line
            td.each(function(){

                var tx = $(this).html(); //get line content
                var pattern = new RegExp(query,"gi");
                var matches  = pattern.exec(tx); //search
                if(matches){ //if some content match replace by a hightlighted span
                    var newtx = tx.replace(matches[0],'<span class="yellowTS" style="color:'+args.color+';background-color:'+args.bgcolor+'">'+matches[0]+'</span>');
                    if($(this).children().is('a')){
                        $(this).children('a').html(newtx);
                    }
                    else {
                        $(this).html(newtx);
                    }
                    //display cell that match
                    $(this).show();
                    //display line that match
                    $(this).parent().show();
                }
            });
         });
        return false;
       });

      $("#clearTS").on('click',function(){
        //display all lines of all tables
            tables.each(function(){
                var tr = $(this).find('td').parent();
                tr.each(function(){
                    $(this).show();
                });
            });
            //remove hightlights
            $('.yellowTS').each(function(){
                var tx = $(this).text();
                $(this).replaceWith(tx);
            });
      });


 
      // eoc
      return this;
    };
 
  
}); 