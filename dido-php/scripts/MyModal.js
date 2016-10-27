var MyModal = {
	MyModalId: 'MyModal', 
	busy: false,
	init: function(){
		if( $('#'+MyModal.MyModalId).length == 0 ){
			$(  '<div class="modal fade" id="'+MyModal.MyModalId+'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">'+
					'<div class="modal-dialog">'+
						'<div class="modal-content">'+
							'<div class="modal-header">'+
								'<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>'+
								'<h4 class="modal-title" id="myModalLabel"></h4>'+
							'</div>'+
							'<div class="modal-body"></div>'+
							'<div class="modal-result"></div>'+
							'<div class="modal-footer">'+
								'<button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-backward fa-1x fa-fw"></span> Torna indietro</button>'+
							'</div>'+
			            '</div>'+
					'</div>'+
				'</div>').appendTo("#page-wrapper");
			$('#'+MyModal.MyModalId).on('hidden.bs.modal', function (e) {
				$('#'+MyModal.MyModalId).remove();
			});
		}
	},
	setTitle: function(title){
		MyModal.init();
		$('#'+MyModal.MyModalId+' .modal-title').html(title);
	},
	addButtons: function(buttons){
		for (i in buttons){
			var button = buttons[i];
			
			var htmlButton = $('<button type="'+(button.type == undefined ? 'button' : button.type)+'" class="btn '+(button.cssClass == undefined ? 'btn-default' : button.cssClass)+'" id="'+button.id+'" '+(button.moreData == undefined ? '' : button.moreData)+'><span class="fa '+(button.spanClass == undefined ? 'fa-arrow-circle-right' : button.spanClass)+' fa-1x fa-fw"></span> '+button.label+'</button>')
				.appendTo('.modal-footer');
			if (button.callback  && typeof(button.callback) === "function"){
				htmlButton.click( button.callback );
			}
		}
	},
	setContent: function(html){
		$('#'+MyModal.MyModalId+' .modal-body').html(html);
	},
	load: function(anchor, callbackSuccess, callbackFailure){
		if(anchor.prop('href') != undefined){
			var href = anchor.prop('href');
	
			var span = anchor.children("span");
			var oldClass = span.prop('class');
			var newClass = "fa fa-refresh fa-spin fa-1x fa-fw";
	
			MyModal.init();
			
			if(MyModal.busy == false){
				MyModal.busy = true;
				span.attr('class', newClass);

				$.ajax({
					url: href, 
					success: function( result ) {
						MyModal.busy = false;
						span.attr('class', oldClass);
						$('#'+MyModal.MyModalId+' .modal-body').html(result);
						MyModal.modal();
						if (callbackSuccess && typeof(callbackSuccess) === "function") callbackSuccess(result);
					},error: function( result ){
						MyModal.busy = false;
						span.attr('class', oldClass);
						MyModal.error("Errore imprevisto");
						if (callbackFailure && typeof(callbackFailure) === "function") callbackFailure(result);
					}
				});
			} 
		}
	},
	submit:function (element,href, data){
		
		var span = element.children("span");
		var oldClass = span.prop('class');
		var newClass = "fa fa-refresh fa-spin fa-1x fa-fw";

		if(MyModal.busy == false){
			MyModal.busy = true;
			span.attr('class', newClass);

			$.ajax({
				url: href,
				type: "POST", 
				dataType: "json",
				data: data,
				success: function( result ) {
					MyModal.busy = false;
					span.attr('class', oldClass);
					if(result.errors){
						MyModal.error(result.errors,true);
					} else {
						MyModal.success();
						$('#'+MyModal.MyModalId+' button[type="submit"]').remove();
						$('#'+MyModal.MyModalId+' button[data-dismiss="modal"]').click(function(){
							$("#myModal").modal('hide');
							location.reload();
						});
					}
				},
				error: function( result ){
					MyModal.busy = false;
					span.attr('class', oldClass);
					MyModal.error("Errore imprevisto");
				}
			});
		} 
	},
	error: function (message, innerdiv){
		MyModal._resultMessage(message, true, innerdiv);
		MyModal.modal();
	},
	success: function(innerdiv){
		MyModal._resultMessage(null, false, innerdiv);
		MyModal.modal();
	},
	_resultMessage: function(message, error, innerdiv){
		MyModal.init();
		$('#'+MyModal.MyModalId+(innerdiv == undefined ? ' .modal-result' : ' '+innerdiv))
			.html(error ? 
				"<div class=\"alert alert-danger\"><p><span class=\"fa fa-warning\">&nbsp;</span> Attenzione, operazione non riuscita<br/><br/>"+message+"</p></div>" : 
				"<div class=\"alert alert-success\"><p><span class=\"glyphicon glyphicon-ok\">&nbsp;</span> Operazione andata a buon fine</p></div>");
	},
	modal: function(){
		if(!$('#'+MyModal.MyModalId).is(':visible')){
			$('#'+MyModal.MyModalId).modal({
				backdrop: 'static'
			});
		}
	},
	close: function(){
		$('#'+MyModal.MyModalId).modal('hide');
	}	
};
