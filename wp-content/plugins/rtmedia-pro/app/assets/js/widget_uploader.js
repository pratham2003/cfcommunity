/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

var   uploaderObj = {};

jQuery(function($) {

   function renderUploaderWidget(widget_id) {

    //sidebar widget uploader config script
    if ($("#rtMedia-upload-button-" + widget_id).length > 0) {
        var temp1 = "rtMedia_widget_plupload_config_"+widget_id;
        uploaderObj[widget_id] = new UploadView(eval(temp1));

        uploaderObj[widget_id].initUploader(false);


        uploaderObj[widget_id].uploader.bind('UploadComplete', function(up, files) {
            activity_id = -1;
            //galleryObj.reloadView();
        });
        
        uploaderObj[widget_id].uploader.bind('FilesAdded', function(up, files) {
            var upload_size_error = false;
            var upload_error = "";
            var upload_error_sep = "";
            var upload_remove_array= [];
            $.each(files, function(i, file) {
                var hook_respo = rtMediaHook.call('rtmedia_js_file_added', [up,file, "#rtMedia-queue-list-"+widget_id+" tbody"]);
                if( hook_respo == false){
                    file.status = -1;
                    upload_remove_array.push(file.id);
                    return true;
                }
                if (uploaderObj[widget_id].uploader.settings.max_file_size < file.size) {
                    return true;
                }
                tdName = document.createElement("td");
                tdName.innerHTML = file.name;
                tdStatus = document.createElement("td");
                tdStatus.className = "plupload_file_status";
                tdStatus.innerHTML = "0%";
                tdSize = document.createElement("td");
                tdSize.className = "plupload_file_size";
                tdSize.innerHTML = plupload.formatSize(file.size);
                tdDelete = document.createElement("td");
                tdDelete.innerHTML = "&times;";
                tdDelete.title = "rtmedia_close";
                tdDelete.className = "close plupload_delete_"+widget_id;
                tr = document.createElement("tr");
                tr.id = file.id;
                tr.appendChild(tdName);
                tr.appendChild(tdStatus);
                tr.appendChild(tdSize);
                tr.appendChild(tdDelete);
                $("#rtMedia-queue-list-"+widget_id).append(tr);
                //Delete Function
                $("#" + file.id + " td.plupload_delete_"+widget_id).click(function(e) {
                    e.preventDefault();
                    uploaderObj[widget_id].uploader.removeFile(up.getFile(file.id));
                    $("#" + file.id).remove();
                    return false;
                });

            });
            if (upload_size_error) {
                // alert(upload_error + " because max file size is " + plupload.formatSize(uploaderObj[widget_id].uploader.settings.max_file_size) );
            }
            $.each(upload_remove_array, function(i, rfile) {
                up.removeFile(up.getFile(rfile));
            });

            rtMediaHook.call( 'rtmedia_pro_js_after_files_added', [up, files, widget_id] );
        });

        uploaderObj[widget_id].uploader.bind( 'UploadComplete', function ( up, files ) {
            var hook_respo = rtMediaHook.call( 'rtmedia_pro_js_after_files_uploaded' );
        } );
        
        uploaderObj[widget_id].uploader.bind('Error', function(up, err) {
            console.log(err.code);
            
            if(err.code == -600){ //file size error // if file size is greater than server's max allowed size
                var tmp_array;
                var ext = tr = '';
                tmp_array =  err.file.name.split(".");
                if(tmp_array.length > 1){
                    ext= tmp_array[tmp_array.length - 1];
                    if( !(typeof(up.settings.upload_size) != "undefined" && typeof(up.settings.upload_size[ext]) != "undefined" &&  typeof(up.settings.upload_size[ext]['size']) )){
                        tr = "<tr class='upload-error'><td>" + err.file.name + "</td><td> " + rtmedia_max_file_msg + plupload.formatSize( up.settings.max_file_size / 1024 * 1024) + " <i class='rtmicon-info-circle' title='" + window.file_size_info + "'></i></td><td>" + plupload.formatSize(err.file.size) + "</td><td class='close error_delete'>&times;</td></tr>";
                    }
                }
                //append the message to the file queue
                $("#rtMedia-queue-list-"+widget_id+" tbody").append(tr);
            }
            else { 
            
                if( err.code == -601) { // file extension error 
                    err.message = rtmedia_file_extension_error_msg;
                }
                var tr = "<tr class='upload-error'><td>" + (err.file ? err.file.name : "") + "</td><td>" + err.message + " <i class='rtmicon-info-circle' title='" + window.file_extn_info + "'></i></td><td>" + plupload.formatSize(err.file.size) + "</td><td class='close error_delete'>&times;</td></tr>";
                $("#rtMedia-queue-list-"+widget_id+" tbody").append(tr); 
            }
                   
            jQuery('.error_delete').on('click',function(e){
                e.preventDefault();
                jQuery(this).parent('tr').remove();
            });
            return false;
            
        });

        uploaderObj[widget_id].uploader.bind('QueueChanged', function(up) {
            var hook_respo = rtMediaHook.call( 'rtmedia_pro_js_after_queue_changed' );
            if( hook_respo != false ) {
                uploaderObj[widget_id].uploadFiles();
            }
        });

        uploaderObj[widget_id].uploader.bind('UploadProgress', function(up, file) {
            $("#" + file.id + " .plupload_file_status").html( rtmedia_uploading_msg + '( ' + file.percent + '% )');
            $("#" + file.id).addClass('upload-progress');
            if (file.percent == 100) {
                 $("#" + file.id).toggleClass('upload-success');
            }
        });
        uploaderObj[widget_id].uploader.bind('BeforeUpload', function(up, file) {
            up.settings.multipart_params.privacy = $("#rtm-file_upload-ui-"+widget_id+" select.privacy").val();
            if (jQuery("#rt_upload_hf_redirect_"+widget_id).length > 0)
                up.settings.multipart_params.redirect = up.files.length;
            jQuery("#rtmedia-uploader-form-"+widget_id+" input[type=hidden]").each(function() {
                up.settings.multipart_params[$(this).attr("name")] = $(this).val();
            });
            up.settings.multipart_params.activity_id = activity_id;
            if ($('#album-list-'+widget_id).length > 0)
                up.settings.multipart_params.album_id = $('#album-list-'+widget_id).find(":selected").val();
            else if ($('#rtmedia-current-album-'+widget_id).length > 0)
                up.settings.multipart_params.album_id = $('#rtmedia-current-album-'+widget_id).val();
        });

        uploaderObj[widget_id].uploader.bind('FileUploaded', function(up, file, res) {

            if (/MSIE (\d+\.\d+);/.test(navigator.userAgent)) { //test for MSIE x.x;
                var ieversion=new Number(RegExp.$1) // capture x.x portion and store as a number

                   if(ieversion <10) { //fixes the bug for IE<10
                           if( typeof res.response !== "undefined" )
                               res.status = 200;
                   }
            }

            var rtnObj;
             try {
                rtnObj = JSON.parse(res.response);
                uploaderObj[widget_id].uploader.settings.multipart_params.activity_id = rtnObj.activity_id;
                activity_id = rtnObj.activity_id;
                console.log(activity_id);
            } catch (e) {
                 console.log('Invalid Activity ID');
            }
            if (res.status == 200 || res.status == 302) {
                if (uploaderObj[widget_id].upload_count == undefined)
                    uploaderObj[widget_id].upload_count = 1;
                else
                    uploaderObj[widget_id].upload_count++;

                if (uploaderObj[widget_id].upload_count == up.files.length && jQuery("#rt_upload_hf_redirect_"+widget_id).length > 0 && jQuery.trim(rtnObj.redirect_url.indexOf("http") == 0)) {
                    window.location = rtnObj.redirect_url;
                }
                $("#" + file.id + " .plupload_file_status").html( rtmedia_uploaded_msg);
                rtMediaHook.call( 'rtmedia_pro_js_after_file_upload', [up, file, res.response] );
            }else {
                $("#" + file.id + " .plupload_file_status").html( rtmedia_upload_failed_msg );
            }

            files = up.files;
            lastfile = files[files.length - 1];

        });

        uploaderObj[widget_id].uploader.refresh();//refresh the uploader for opera/IE fix on media page

        $("#rtMedia-start-upload-"+widget_id).click(function(e) {
            uploaderObj[widget_id].uploadFiles(e);
        });
        $("#rtMedia-start-upload-"+widget_id).hide();
    }

   }

    //
    $('.widget-drag-drop').each(function () {

        var temp = this.id.split("-");//get the widget id
        var widget_id = temp[temp.length-1];
        renderUploaderWidget(widget_id);

    });


});
