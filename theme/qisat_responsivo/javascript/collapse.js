/**
 * Created by leonardo on 15/09/2016.
 */

$(document).ready(function(){

    $( ".collapse-sign" ).click(function() {

        var collapsesequence = this.dataset.collapsesequence.split(",");
        var collapsesubsequence = this.dataset.collapsesubsequence.split(",");

        for (var i = 0; i < collapsesequence.length; i++) {
            if ($("#module-" + collapsesequence[i]).is( ":visible" )) {
                $("#module-" + collapsesequence[i]).hide("slow");
                this.src = this.src.replace("minus", "plus");
                $("#collapse-sign-" + collapsesequence[i]).attr("src",this.src);

                for (var k = 0; k < collapsesubsequence.length; k++) {
                    $("#module-" + collapsesubsequence[k]).hide("slow");
                }
            }else{
                $("#module-" + collapsesequence[i]).show("slow");
                this.src = this.src.replace("plus", "minus"); ;
            }
        }
    });

});