(function( $ ) {
"use strict";

	$('.wpeiePro #upload').attr('disabled','disabled');
    $(".wpeiePro .wpeieProFile").on('change',function () {
        var wpeifileExtension = ['xls', 'xlsx'];
        if ($.inArray($(this).val().split('.').pop().toLowerCase(), wpeifileExtension) === -1) {
            alert("Only format allowed: "+wpeifileExtension.join(', '));	
			$(".wpeiePro input[type='submit']").attr('disabled','disabled');
        }else{
			$(".wpeiePro input[type='submit']").removeAttr('disabled');
			$(".wpeiePro").find('form').submit();
		}
    });
	
	$(".wpeiePro #product_import").on("submit", function (e) {
		e.preventDefault();
				var wpeiData = new FormData();
				$.each($('.wpeieProFile')[0].files, function(i, file) {
					wpeiData.append('file', file);
				});	
				wpeiData.append('_wpnonce',$("#_wpnonce").val());
				wpeiData.append('importProducts',$("#importProducts").val() );
				var url= window.location.href;
				$.ajax({
					url: window.location.href,
					data: wpeiData,
					cache: false,
					contentType: false,
					processData: false,
					type: 'POST',
					beforeSend: function() {	
						$("html, body").animate({ scrollTop: 0 }, "slow");
						$('.wpeiePro').addClass('loading');	
					},					
					success: function(response){
						$(".wpeiePro .result").slideDown().html($(response).find(".result").html());
						$('.wpeiePro').removeClass('loading');	
						$("#product_import").fadeOut();	
						
						wpeiDragDrop();

						automatch_columns();
						
						$('.wpeiePro input#update_only').on("change",function(){
							if ($(this).is(':checked')) {
								$('.wpeiePro #productupdateBy').show();
								$('.wpeiePro .hideOnUpdateById').hide();	
							}else{
								$('.wpeiePro .hideOnUpdateById').show();	
							}
						});
						
						
						$(".wpeiePro #product_process").on('submit',function(e) {
							e.preventDefault();
							if($("input[name='post_title']").val() !='' || $("input[name='ID']").val() !='' || $("input[name='_sku']").val() !='' ){								
								$(".progressText").fadeIn();
								var total = $(".wpeiePro input[name='finalupload']").val() ;
								$(".wpeiePro .total").html(total-1);
								var i = 2;	
								$('.wpeiePro').addClass('loading');
								
								function wpeiImportProducts() {									
									var start = parseInt($(".wpeiePro input[name='start']").val() ,10 );
									var total = parseInt( $(".wpeiePro input[name='finalupload']").val(),10 ) ;
									if(start > total  ){
										$('.wpeiePro .success , .wpeiePro .error, .wpeiePro .warning').delay(2000).hide();
										$(".wpeiePro #product_import").delay(5000).slideDown();
									}else{	
										
										$.ajax({
											url: wpeiePro.ajax_url,
											data: $(".wpeiePro #product_process").serialize(),
											type: 'POST',
											beforeSend: function() {
												$("html, body").animate({ scrollTop: 0 }, "slow");												
												$(".wpeiePro #product_process").hide();
											},						
											success: function(response){
												
												$(".wpeiePro .importMessage").slideDown().html($(response).find(".importMessage").html());
												$(".wpeiePro .ajaxResponse").html(response);
												$(".wpeiePro .thisNum").html($("#AjaxNumber").html() );
												
													$(".wpeiePro input[name='start']").val(i + 1 );
													i++;
													
											},complete: function(response){
													$('.wpeiePro').removeClass('loading');
													wpeiImportProducts();
											}
										});	
									
									}
								}
								
								wpeiImportProducts();
							}else alert('Title Selection, SKU or Product ID (for update from export file) is Mandatory.');
							
						});							
					}
			});			
	});	

			//drag and drop
			function wpeiDragDrop(){
				$('.wpeiePro .draggable').draggable({cancel:false});
				$( ".wpeiePro .droppable" ).droppable({
				  drop: function( event, ui ) {
					$( this ).addClass( "ui-state-highlight" ).val( $( ".ui-draggable-dragging" ).val() );
					$( this ).attr('value',$( ".ui-draggable-dragging" ).attr('key')); //ADDITION VALUE INSTEAD OF KEY	
					$( this ).val($( ".ui-draggable-dragging" ).attr('key') ); //ADDITION VALUE INSTEAD OF KEY
					$( this ).attr('placeholder',$( ".ui-draggable-dragging" ).attr('value')); 				
					$( ".ui-draggable-dragging" ).css('visibility','hidden'); //ADDITION + LINE
					$( this ).css('visibility','hidden'); //ADDITION + LINE
					$( this ).parent().css('background','#90EE90');						
					
					if($("input[name='ID']").hasClass('ui-state-highlight') ){
						$(".hideOnUpdateById").hide();
					}
					
				  }		 
				});		
			}
			wpeiDragDrop();
			
			
			function automatch_columns(){
				
				$(".wpeiePro #automatch_columns").on("change",function(){

					if($(".wpeiePro #automatch_columns").is(':checked')){
						
						$( ".wpeiePro .draggable" ).each(function(){
							
							var key = $( this ).attr('key') ; 
							key = key.toUpperCase();
							key = key.replace(" ", "_");							
							var valDrag = $( this ).val() ;
							
							
							$( ".wpeiePro .droppable" ).each(function(){
								
								var valDrop = $( this ).val();
								
								var drop = $( this ).attr('name');
								
								drop.indexOf( '_' ) == 0 ? drop = drop.replace( '_', '' ) : drop;
																
								var drop = drop.replace(/_/g, " ");								
								var nameDrop = drop.toUpperCase();
																
								if( valDrag == nameDrop ){
								
									$( this ).val( key );
									
									//$( valDrag ).css('visibility','hidden'); //ADDITION + LINE
									$( this ).css('background','#90EE90');
									$( this ).parent().css('background','#90EE90');	
								}
							});							
						});

						alert("Check your automatch - The letter after the match signifies the Excel Column Letter. If not satisfied you can always uncheck auto match and do manually");
				
					}else{
						$( ".wpeiePro .droppable" ).val('');
						$( ".wpeiePro .droppable" ).css('background','initial');
						$( ".wpeiePro .droppable" ).parent().css('background','initial');	
					}
												
				});			
			}
			automatch_columns();
			

	

			
			
		
			
			$(".wpeiePro #selectTaxonomy").on("change", function () {		
				$(".wpeiePro #selectTaxonomy").submit();
			});
			
			$(".wpeiePro #selectTaxonomy").on("submit", function (e) {		
				e.preventDefault();
				localStorage.setItem('taxonomy',$("#selectTaxonomy #vocabularySelect ").val() );
				var wpeiData = $(this).serialize();
					$.ajax({
						url: $(this).attr('action'),
						data:  wpeiData,
						type: 'POST',
						beforeSend: function() {								
							$('.wpeiePro').addClass('loading');
						},						
						success: function(response){
							$(".wpeiePro .vocabularySelect").slideDown().html($(response).find(".vocabularySelect").html());
							$('.wpeiePro').removeClass('loading');
							$(".wpeiePro #selectTaxonomy #vocabularySelect ").val(localStorage.getItem('taxonomy'));
						}
					});				
			});
			
			$(".wpeiePro .exportToggler").on('click',function(){
				$(".wpeiePro #exportProductsForm").slideToggle();
				$(".wpeiePro .exportTableWrapper").slideToggle();
				$(".wpeiePro .downloadToExcel").slideToggle();
				$(".wpeiePro #selectTaxonomy").slideToggle();
			});



			
			$(".wpeiePro #exportProductsForm").on('submit',function(e) {
					e.preventDefault();
		
						
				//if checkbox is checked
				$(".wpeiePro .fieldsToShow").each(function(){
					if($(this).is(':checked')){
					}else localStorage.setItem($(this).attr('name') ,$(this).attr('name') );
				});	
				
				$.ajax({
					url: $(this).attr('action'),
					data:  $(this).serialize(),
					type: 'POST',
					beforeSend: function() {									
						$('.wpeiePro').addClass('loading');		
					},						
					success: function(response){

										
						$('.wpeiePro').removeClass('loading');
						
						$(".wpeiePro #exportProductsForm").hide();
						$(".wpeiePro #selectTaxonomy").hide();	
						
						
						
						if( wpeiePro.exportMethod !='' && wpeiePro.exportMethod == '1' ){
												
							$(".resultExport").slideDown().html($(response).find(".error").hide());	
							$(".resultExport").slideDown().html($(response).find(".success").show());
							$(".resultExport").slideDown().html($(response).find(".resultExport a.exportExcel").trigger("click"));
							
						}else{
								$(".resultExport").slideDown().html($(response).find(".resultExport").html());					
								//if checkbox is checked
								$(".wpeiePro .fieldsToShow").each(function(){									
									if (localStorage.getItem($(this).attr('name')) ) {
										$(this).attr('checked', false);
									}//else $(this).attr('checked', false);							
									localStorage.removeItem($(this).attr('name'));	
								});	
									
									var i=0;
									$(".wpeiePro input[name='total']").val($(".wpeiePro .totalPosts").html());
									$(".wpeiePro input[name='start']").val($(".wpeiePro .startPosts").html());							
									var total = $(".wpeiePro input[name='total']").val();	
									var start = $(".wpeiePro input[name='start']").val();
									progressBar(start,total) ;

								function wpeiDataExportProducts() {
									var total = $(".wpeiePro input[name='total']").val();
									var start = $(".wpeiePro input[name='start']").val() * i;
									
									if($(".wpeiePro .totalPosts").html()  <=100){
											$(".wpeiePro input[name='posts_per_page']").val($(".wpeiePro .totalPosts").html() );
									}else $(".wpeiePro input[name='posts_per_page']").val($(".wpeiePro .startPosts").html());
									
									var dif = total- start;
									
									if( $('.wpeiePro #toExport >tbody >tr').length >= total ){
																				
										
										$('.wpeiePro #myProgress').delay(10000).hide('loading');


										$("body").find('#exportProductsForm').find("input[type='number'],input[type='text'], select, textarea").val('');
										$('.wpeiePro .message').html('Job Done!');
										$('.wpeiePro .message').addClass('success');
										$('.wpeiePro .message').removeClass('error');
										
										if( wpeiePro.exportMethod !='' && wpeiePro.exportMethod=='2' ){
											
											$(".wpeiePro #toExport").table2excel({
												exclude: ".noExl",
												name: "Excel Document Name",
												filename: "export" + new Date().toISOString().replace(/[\-\:\.]/g, ""),
												fileext: ".xlsx",
												exclude_img: true,
												exclude_links: true,
												exclude_inputs: true
											});												
										}else{
											$(".wpeiePro #toExport").tableExport();																					
										}
										
									}else{	
									
										var dif = total - start;
										if(total> 100 && dif <=100 ){
											$(".wpeiePro  input[name='posts_per_page']").val(dif);
										} 									
										
										$.ajax({
											url: wpeiePro.ajax_url,
											data: $(".wpeiePro #exportProductsForm").serialize(),
											type: 'POST',
											beforeSend: function() {
												//$("html, body").animate({ scrollTop: 0 }, "slow");	
												$('.wpeiePro').removeClass('loading');
											},						
											success: function(response){	
										 
												$(".wpeiePro .tableExportAjax").append(response);
												i++;
												start = $(".wpeiePro input[name='start']").val() * i;
												
												$(".wpeiePro  input[name='offset']").val(start);
												
												var offset = $(".wpeiePro  input[name='offset']").val();																									
												progressBar(start,total) ;	
												
											},complete: function(response){	
																					
													wpeiDataExportProducts();	
												
											}
										});
									}
								}
								wpeiDataExportProducts();
						}//choose between export methods
					}
					});	


										
			});	
	
	
    $('.wpeiePro #check_all1').on('change',function(){
        var checkboxes = $('.wpeiePro .tax_checks').find(':checkbox');
        if($(this).prop('checked')) {
          checkboxes.prop('checked', true);
        } else {
          checkboxes.prop('checked', false);
        }
    });
    $('.wpeiePro #check_all2').on('change',function(){
        var checkboxes = $('.wpeiePro .fields_checks').find(':checkbox');
        if($(this).prop('checked')) {
          checkboxes.prop('checked', true);
        } else {
          checkboxes.prop('checked', false);
        }
    });	

			$(".wpeiePro .imageHandling").on('click',function(){
				$(".wpeiePro .imageinfo").slideToggle();
				$(".wpeiePro .productInfo").hide();
				$(".wpeiePro .updateinfo").hide();				
			});
			$(".wpeiePro .productHandling").on('click',function(){
				$(".wpeiePro .productInfo").slideToggle();	
				$(".wpeiePro .imageinfo").hide();
				$(".wpeiePro .updateinfo").hide();
			});			
			$(".wpeiePro .updateHandling").on('click',function(){
				$(".wpeiePro .productInfo").hide();	
				$(".wpeiePro .imageinfo").hide();
				$(".wpeiePro .updateinfo").slideToggle();
			});	


										
			function progressBar(start,total) {
				var width = (start/total) * 100;
				var elem = document.getElementById("myBar");   
				if (start >= total-1) {
				  elem.style.width = '100%'; 
				} else {
				  start++; 
				  elem.style.width = width + '%'; 
				}
			}
			jQuery(document).ready(function(){
				$('.upload_file').click(function(e){
						e.preventDefault();
					$('.wap_upload_filed').toggleClass('show');
				});
			});
			
			
})( jQuery )