addLoadEvent(function(){

	multiUpload = {

		i:0,

		initUploadForm: function() {
			var f = document.getElementById('upload-file');
			var field_name = document.getElementById('upload');
			var field_title = document.getElementById('post_title');
			var field_content = document.getElementById('post_content');
			field_name.setAttribute('name','image[]');
			field_title.setAttribute('name','post_title[]');
			field_content.setAttribute('name','post_content[]');
			
			var extraRows = '';
			extraRows += '<tr class="resize"><th scope="row">Resize</th>';
			extraRows += '<td><label for="image_size_orig" class="inline">Original:</label><input type="text" id="image_size_orig" name="image_size_orig[]" value="" />';
				extraRows += '<label for="image_size_thumb">Thumb:</label><input type="text" id="image_size_thumb" name="image_size_thumb[]" value="128" /></td></tr>';
			new Insertion.Before('buttons', extraRows);

			var newRows = '';
			newRows += '<tr id="addfield"><td colspan="2">[<a href="#" onclick="multiUpload.appendForm(); return false;">+add field</a>]';
			new Insertion.Before('buttons', newRows);
		},


		appendForm: function() {
			var newRows = '';
			newRows += '<tr><td colspan="2">[<a href="#" onclick="multiUpload.removeForm(this); return false;">- remove</a>]';
			newRows += '<table><col /><col class="widefat" />';
			newRows += '<tr><th scope="row"><label for="upload' + this.i + '">File</label></th>'
			newRows += '<td><input type="file" id="upload' + this.i + '" name="image[]" /></td></tr>';
			newRows += '<tr><th scope="row"><label for="post_title' + this.i + '">Title</label></th>';
			newRows += '<td><input type="text" id="post_title' + this.i + '" name="post_title[]" value="" /></td></tr>';
			newRows += '<tr><th scope="row"><label for="post_content' + this.i + '">Description</label></th>';
			newRows += '<td><textarea name="post_content[]" id="post_content"></textarea></td></tr>';
			newRows += '<tr class="resize"><th scope="row">Resize</th>';
			newRows += '<td><label for="image_size_orig' + this.i + '">Original:</label><input type="text" id="image_size_orig' + this.i + '" name="image_size_orig[]" value="" />';
				newRows += '<label for="image_size_thumb' + this.i + '">Thumb:</label><input type="text" id="image_size_thumb' + this.i + '" name="image_size_thumb[]" value="128" /></td></tr>';

			new Insertion.Before('addfield', newRows);
			this.i++;
		},
		
		removeForm: function(ele) {
			var f = ele.parentNode;
			f.parentNode.removeChild(f);
		}
		
	}
	
	multiUpload.initUploadForm();

});