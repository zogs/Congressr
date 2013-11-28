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
       $("#tsearch-form").on("submit",function(){

          //delete yellow hightlight
          $(".tsearch-hightlight").each(function(){
              var tx = $(this).text();
              $(this).replaceWith(tx);
          });

          //get query
          var query = $("#tsearch-query").val();
          var nbLinesMatches = 0;
          var matches = null;
          var nbMatches = 0;
          var pattern = new RegExp(query,"gi");
          var match = false;
          //for each tables
         tables.each(function(){

            var trs = $(this).find('tr');
            trs.each(function(){

                    var tr = $(this);
                    var td = tr.find('td');            
                    
                    tr.hide();
                    //for each line
                    td.each(function(){
                          
                          var content = $(this).html(); //get line content
                         
                          matches  = pattern.exec(content); //search
                          
                          if(matches){ //if some content match replace by a hightlighted span
                                  nbMatches++;
                                  match = true;
                                  var newtx = content.replace(matches[0],'<span class="tsearch-hightlight" style="color:'+args.color+';background-color:'+args.bgcolor+'">'+matches[0]+'</span>');
                                  if($(this).children().is('a')){
                                        $(this).children('a').html(newtx);
                                  }
                                  else {
                                        $(this).html(newtx);
                                  }
                                  //display cell that match
                                  $(this).show();
                                  tr.show();
                          }
                  });
                  if(match==true) {
                          nbLinesMatches++;
                  }
                  match = false;
            });

              
         });

      
      $("#tsearch-results").empty().append(nbMatches+" résultats, "+nbLinesMatches+" lignes");

        return false;
       });

      $("#tsearch-clear").on('click',function(){
        //display all lines of all tables
            tables.each(function(){
                var tr = $(this).find('td').parent();
                tr.each(function(){
                    $(this).show();
                });
            });
            //remove hightlights
            $('.tsearch-hightlight').each(function(){
                var tx = $(this).text();
                $(this).replaceWith(tx);
            });
            //reset search field
            $('#tsearch-query').val('');
            //reset search results
            $("#tsearch-results").empty();
      });


 
      // eoc
      return this;
    };
 
  
}); 