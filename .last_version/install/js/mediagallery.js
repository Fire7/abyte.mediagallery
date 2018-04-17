var abyteMG = {
    youTubeAPIKey: 'AIzaSyBSERAPIjD6PtKVf1-Naiz4mPTL_0zLMUs'
};

abyteMG.getYouTubeData = function (id, cb) {
    $.get("https://www.googleapis.com/youtube/v3/videos?part=snippet&id=" + id + "&key=" + abyteMG.youTubeAPIKey, cb);
}

//  *********************************************************** functions block ***********************************************************
abyteMG.CentriredModalWindow = function (selector, skipOpen){
    var elem = $(selector);
    var modalH = elem.height(),
        modalW = elem.width();

    elem.removeAttr('style');
    elem.css({
        "display":"block",
        "opacity": skipOpen ? 1 : 0,
        "margin-left":"-"+(parseInt(modalW)/2)+"px",
        "margin-top":"-"+(parseInt(modalH)/2)+"px",
 //       "width": width ? modalW+'px'

    });


}

abyteMG.OpenModalWindow = function (selector){
    $('#mg_bgmod').css("display","block");
    $(selector).animate({"opacity":1},300);
}

abyteMG.CloseModalWindow = function (){
    $('#mg_bgmod').css("display","none");
    var modal = $('.mg_modal');
    modal.css({"opacity":1});
    modal.animate({"opacity":0},300);
    setTimeout(function() { modal.css({"display":"none"}); }, 500);
}


//  *********************************************************** search block ***********************************************************
abyteMG.submit_search_form = function (form) {
    return true;
}

//  *********************************************************** album block ************************************************************
abyteMG.edit_video_album = function (id,name) {

    $('#mediagallery_album_edit_id').val(id||'');
    $('#mediagallery_album_edit_name').val(name||'');

    if (!id)
    {
        $('#mediagallery_album_edit>.mg_title').html(BX.message('CREATE_NEW_ALBUM'));
    }
    else
    {
        $('#mediagallery_album_edit_title>.mg_title').html(BX.message('EDIT_ALBUM'));
    }
    abyteMG.CentriredModalWindow('#mediagallery_album_edit');
    abyteMG.OpenModalWindow('#mediagallery_album_edit');

    return false;

}

abyteMG.delete_album = function (id, callbackUrl) {
    $.post(
        document.getElementById('mediagallery_album_edit_form').action,
        {
            action : 'section_delete',
            mediagallery_ajax : 'Y',
            sessid: BX.bitrix_sessid(),
            ID:id,
        },
        function (data) {
            if (!data.ERROR.length) {
               if (callbackUrl)
                location = callbackUrl;
               else 
                location.reload();
            }
            else alert (data.ERROR.join('/n'));

        },
        "json"
    );
}

abyteMG.submit_edit_form = function (form) {
    var $this =  $(form),
        dataAjax = {
            action : 'section_update'
        },
        dataForm = $this.serializeArray();

    for (var i = dataForm.length; i--;) {
        dataAjax[dataForm[i].name] = dataForm[i].value;
    }

    $.post(
        $this[0].action,
        dataAjax,
        function (data) {
            if (!data.ERROR.length)
            {
                location.reload();
                abyteMG.CloseModalWindow('#mediagallery_album_edit');
            }
            else alert (data.ERROR.join('/n'));
        },
        "json"
    );

}



//  *********************************************************** video block ************************************************************
abyteMG.onVideoAddSubmit = function (form) {
    var $this = $(form),
        err = [],
        $submitBtn = $this.find('.ab_mg_video_submit');

    if ($submitBtn.hasClass('ab_mg_btn--loading'))
        return false;

    if (!document.getElementById('ab_mg_file_name').value) {
        err.push (BX.message('EMPTY_NAME_ERROR'));
    }

    if (
        !document.getElementById('ab_mg_add_file').value && !document.getElementById('ab_mg_file_link').value && !document.getElementById('ab_mg_file').value) {
        err.push (BX.message('EMPTY_DATA_ERROR'));
    }

    if (document.getElementById('ab_mg_section_id') &&
        document.getElementById('ab_mg_section_id').value === "NEW" &&
        !document.getElementById('ab_mg_section_name').value) {
        err.push (BX.message('EMPTY_ALBUM_NAME_ERROR'));
    }

    if (err.length) {
        alert (err.join('\n'));
        return false;
    }
    else {

        $submitBtn.addClass('ab_mg_btn--loading');

        var $this =  $(form),
            dataAjax = {
                action: 'element_update'
            },
            dataForm = $this.serializeArray();

        for (var i = dataForm.length; i--;) {
            dataAjax[dataForm[i].name] = dataForm[i].value;
        }

        $.post(
            $this[0].action,
            dataAjax,
            function (data) {              
                if (!data.ERROR.length) location.reload();
                else {
                    $submitBtn.removeClass('ab_mg_btn--loading');
                    alert (data.ERROR.join('/n'));
                }
            },
            "json"
        );

        return false;

    }
}


abyteMG.onAlbumSelectChange = function (select) {
    if (select.value === "NEW")
    {
        document.getElementById('section_name_holder').style.display = 'block';
    }
    else
    {
        document.getElementById('section_name_holder').style.display = 'none';
    }
}




abyteMG.edit_video = function (obj) {
    obj = obj||{};

    $("#ab_mg_element_id").val(obj.ID||"");
    $("#ab_mg_file_name").val(obj.NAME||"");
    $("#ab_mg_section_id").val(obj.IBLOCK_SECTION_ID||"");
    $("#ab_mg_video_detail_text").val(obj.DETAIL_TEXT||"");

    if (obj.VIDEO_LINK)  $("#ab_mg_file_link").val(obj.VIDEO_LINK).show();
    else $("#ab_mg_file_link").val("").hide();

    $("#ab_mg_file").val(obj.FILE||"");

    $('#mediagallery_form_add').slideDown("slow");

    $('body').animate({
        scrollTop: $("#mediagallery_form_add").offset().top - 100
    }, 1000);


    return false;

}

abyteMG.delete_video = function (id, section_id) {

    $.post(
        document.getElementById('mediagallery_edit_form').action,
        {
            action : 'element_delete',
            mediagallery_ajax : 'Y',
            sessid: BX.bitrix_sessid(),
            ID:id,
            SECTION_ID:section_id
        },
        function (data) {

            if (!data.ERROR.length) location.reload();
            else alert (data.ERROR.join('/n'));
        },
        "json"
    );
}

abyteMG.copy_video = function (id, url, cb) {
    $.post(
        url,
        {
            action : 'element_copy',
            mediagallery_ajax : 'Y',
            sessid: BX.bitrix_sessid(),
            ID:id
        },
        function (data) {
            cb(data);
        },
        "json"
    );
}

abyteMG.resizeImageBox = function (img, mgModal, windowWidth) {

    var imgW = img[0].width,
        imgH = img[0].height,
        imgK = img[0].width / img[0].height,
        windowK = (document.documentElement.clientWidth + (windowWidth || 0)) / document.documentElement.clientHeight;

    if (imgK > windowK) {
        mgModal.css({
            'width' : '100%',
            'height': 'auto'
        });
        img.css({
            'width' : '100%',
            'height': 'auto'
        });

    }
    else  {
        mgModal.css({
            'width' : 'auto',
            'height': '100%'
        });
        img.css({
            'height' : 'calc(100% - 80px)',
            'width' : 'auto'
        });
    }
}

abyteMG.open_video = function (url, skipOpen, scrollRight, skipSection) {

    $('#mg_bgmod').css("display","block").addClass('loading');
    var mgModal = $('#mediagallery_modal');
        maxImageWidth = document.documentElement.clientWidth - 220;
        maxImageHeight = document.documentElement.clientHeight - 80;

    $.post(
        url,
        {
            AJAX_CALL: 'Y',
            SKIP_SECTION: skipSection ? 'Y' : 'N'
        },
        function (data) {
            if (skipOpen) {
                var animateParams = scrollRight ? {"opacity":0, 'left': "+=640px" } : {"opacity":0, 'left': "-=640px" } ;

                mgModal.animate(animateParams,{
                    duration: 150,
                    complete: function () {
                         mgModal.find('>.content').html(data);
                         var img = mgModal.find('.mediagallery_image img');

                        if (img.length)
                            img[0].onload = function () {
                                if ( img[0].width > maxImageWidth || img[0].height > maxImageHeight)
                                    abyteMG.resizeImageBox(img, mgModal, mgModal.find('.mediagallery_marked').length ? -240 : 0);
                                abyteMG.CentriredModalWindow('#mediagallery_modal');
                                abyteMG.OpenModalWindow('#mediagallery_modal');
                            }
                        else {
                            abyteMG.CentriredModalWindow('#mediagallery_modal');
                            mgModal.find('.mediagallery_player').css('marginRight', 0);
                            abyteMG.OpenModalWindow('#mediagallery_modal');
                        }
                    }
                });

            }
            else {

                mgModal.find('>.content').html(data);
                img = mgModal.find('.mediagallery_image img');

                if (img.length)
                  img[0].onload = function () {
                      if ( img[0].width > maxImageWidth || img[0].height > maxImageHeight)
                        abyteMG.resizeImageBox(img, mgModal, mgModal.find('.mediagallery_marked').length ? -240 : 0);
                      abyteMG.CentriredModalWindow('#mediagallery_modal');                      
                      abyteMG.OpenModalWindow('#mediagallery_modal');
                }
                else {
                    abyteMG.CentriredModalWindow('#mediagallery_modal');
                    mgModal.find('.mediagallery_player').css('marginRight', 0);
                    abyteMG.OpenModalWindow('#mediagallery_modal');
                }
            }
            $('#mg_bgmod').removeClass('loading');
        }
    );
    return false;
}

/*
 *  PekeUpload 1.0.6 - jQuery plugin
 *  written by Pedro Molina
 *  http://www.pekebyte.com/
 *
 *  Copyright (c) 2013 Pedro Molina (http://pekebyte.com)
 *  Dual licensed under the MIT (MIT-LICENSE.txt)
 *  and GPL (GPL-LICENSE.txt) licenses.
 *
 *  Built for jQuery library
 *  http://jquery.com
 *
 */

$.fn.pekeUpload = function(options){

    // default configuration properties
    var defaults = {
        onSubmit:       false,
        btnText:        "Browse files...",
        url:        "",
        theme:        "custom",
        field:        "file",
        data:         null,
        multi:        true,
        showFilename:       true,
        showPercent:        true,
        showErrorAlerts:    true,
        allowedExtensions:  "",
        invalidExtError:    "Invalid File Type",
        maxSize:      0,
        sizeError:      "Size of the file is greather than allowed",
        onStartUpload:      function() {},
        onFileError:        function(file,error){},
        onFileSuccess:      function(file,data){}
    };

    var options = $.extend(defaults, options);

    //Main function
    var obj;
    var file = new Object();
    var fileinput = this;
    this.each(function() {
        obj = $(this);
        //HTML code depends of theme
        if (options.theme == "bootstrap"){
            var html = '<a href="javascript:void(0)" class="btn btn-primary btn-upload"> <span class="icon-upload icon-white"></span> '+options.btnText+'</a><div class="pekecontainer"></div>';
        }
        if (options.theme == "custom"){
            var html = '<a href="javascript:void(0)" class="btn-pekeupload ab_mg_btn ab_mg_file-block__action">'+options.btnText+'</a><div class="pekecontainer"></div>';
        }
        obj.after(html);
        obj.hide();
        //Event when clicked the newly created link
        obj.next('a').click(function(){
            obj.click();
        });
        //Event when user select a file
        obj.change(function(){
            if (!obj[0].files || !obj[0].files[0]) //bug?
                return;
            options.onStartUpload();
            obj.next('a').next('div').find('.alert-pekeupload').remove();
            file.name = obj.val().split('\\').pop();
            file.size = (obj[0].files[0].size/1024)/1024;
            if (validateresult()==true){
                if (options.onSubmit==false){
                    UploadFile();
                }
                else{
                    obj.next('a').next('div').prepend('<br /><span class="filename">'+file.name+'</span>');
                    obj.parent('form').bind('submit',function(){
                        obj.next('a').next('div').html('');
                        UploadFile();
                    });
                }
            }
        });
    });
    //Function that uploads a file
    function UploadFile(){
        var error = true;
        if (options.theme=="bootstrap"){
            var htmlprogress = '<div class="file"><div class="filename"></div><div class="progress progress-striped"><div class="bar pekeup-progress-bar" style="width: 0%;"><span class="badge badge-info"></span></div></div></div>';
        }
        if (options.theme=="custom"){
            var htmlprogress = '<div class="file"><div class="filename"></div><div class="progress-pekeupload"><div class="bar-pekeupload pekeup-progress-bar" style="width: 0%;"><span></span></div></div></div>';
        }
        obj.next('a').next('div').prepend(htmlprogress);
        var formData = new FormData();
        formData.append(options.field, obj[0].files[0]);
        formData.append('data', options.data);
        $.ajax({
            url: options.url,
            type: 'POST',
            data: formData,
            // dataType: 'json',
            success: function(data){
                var percent = 100;
                obj.next('a').next('div').find('.pekeup-progress-bar:first').width(percent+'%');
                obj.next('a').next('div').find('.pekeup-progress-bar:first').text(percent+"%");
                if (data==1){
                    if (options.multi==false){
                        obj.attr('disabled','disabled');
                    }
                    options.onFileSuccess(file,data);
                }
                else{
                    options.onFileError(file,data);
                    obj.next('a').next('div').find('.file:first').remove();
                    if((options.theme == "bootstrap")&&(options.showErrorAlerts==true)){
                        obj.next('a').next('div').prepend('<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert"></button> '+data+'</div>');
                        bootstrapclosenotification();
                    }
                    if((options.theme == "custom")&&(options.showErrorAlerts==true)){

                        obj.next('a').next('div').prepend('<div class="alert-pekeupload"><button type="button" class="close" data-dismiss="alert"></button> '+data+'</div>');
                        customclosenotification();
                    }
                    error = false;
                }
            },
            xhr: function() {  // custom xhr
                myXhr = $.ajaxSettings.xhr();
                if(myXhr.upload){ // check if upload property exists
                    myXhr.upload.addEventListener('progress',progressHandlingFunction, false); // for handling the progress of the upload
                }
                return myXhr;
            },
            cache: false,
            contentType: false,
            processData: false
        });
        return error;
    }
    //Function that updates bars progress
    function progressHandlingFunction(e){
        if(e.lengthComputable){
            var total = e.total;
            var loaded = e.loaded;
            if (options.showFilename==true){
                obj.next('a').next('div').find('.file').first().find('.filename:first').text(file.name);
            }
            if (options.showPercent==true){
                var percent = Number(((e.loaded * 100)/e.total).toFixed(2));
                obj.next('a').next('div').find('.file').first().find('.pekeup-progress-bar:first').width(percent+'%');
            }
            obj.next('a').next('div').find('.file').first().find('.pekeup-progress-bar:first').html('<span>'+percent.toFixed(2)+"%</span>");
        }
    }
    //Validate master
    function validateresult(){
        var canUpload = true;
        if (options.allowedExtensions!=""){
            var validationresult = validateExtension();
            if (validationresult == false){
                canUpload = false;
                if((options.theme == "bootstrap")&&(options.showErrorAlerts==true)){
                    obj.next('a').next('div').prepend('<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert"></button> '+options.invalidExtError+'</div>');
                    bootstrapclosenotification();
                }
                if((options.theme == "custom")&&(options.showErrorAlerts==true)){
                    obj.next('a').next('div').prepend('<div class="alert-pekeupload"><button type="button" class="close"></button> '+options.invalidExtError+'</div>');
                    customclosenotification();
                }
                options.onFileError(file,options.invalidExtError);
            }
            else{
                canUpload = true;
            }
        }
        if (options.maxSize>0){
            var validationresult = validateSize();
            if (validationresult == false){
                canUpload = false;
                if((options.theme == "bootstrap")&&(options.showErrorAlerts==true)){
                    obj.next('a').next('div').prepend('<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert"></button> '+options.sizeError+'</div>');
                    bootstrapclosenotification();
                }
                if((options.theme == "custom")&&(options.showErrorAlerts==true)){
                    obj.next('a').next('div').prepend('<div class="alert-pekeupload"><button type="button" class="close" data-dismiss="alert"></button> '+options.sizeError+'</div>');
                    customclosenotification();
                }
                options.onFileError(file,options.sizeError);
            }
            else{
                canUpload = true;
            }
        }
        return canUpload
    }
    //Validate extension of file
    function validateExtension(){
        var ext = obj.val().split('.').pop().toLowerCase();
        var allowed = options.allowedExtensions.split("|");
        if($.inArray(ext, allowed) == -1) {
            return false;
        }
        else{
            return true;
        }
    }
    //Validate Size of the file
    function validateSize(){
        if (file.size > options.maxSize){
            return false;
        }
        else{
            return true;
        }
    }
    //Function that allows close alerts of bootstap
    function bootstrapclosenotification(){
        obj.next('a').next('div').find('.alert-error').click(function(){
            $(this).remove();
        });
    }
    function customclosenotification(){
    }
};


//  *********************************************************** DOCUMENT READY BLOCK ************************************************************

$(document).ready(function () {
    var html = '<div id="mg_bgmod" class="close" style="display: none;"></div><div id="mg_bgmod__loading"></div>';
    html +='<div id="mediagallery_modal" class="mg_modal" style="opacity: 0; display: none;">';
    html +='<div class="content"></div>';
    html += '<span class="mg_modal-close-icon" onclick="abyteMG.CloseModalWindow(\'#mediagallery_modal\');"></span>';
    html+= '</div>';

    $('body').append(html);

    $(window).on('resize', function (e) {
        var mgModal = $('#mediagallery_modal'),
            img = mgModal.find('.mediagallery_image img');
        if (img.length && mgModal.is(':visible')) {
            abyteMG.resizeImageBox(img, mgModal);
            abyteMG.CentriredModalWindow('#mediagallery_modal', true);

        }
    });
});

window['abyteMG'] = abyteMG;
