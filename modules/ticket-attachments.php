<div class="attachment-section">
    <label for="attachments" class="attachment-btn">
        <i class="bi bi-paperclip me-2"></i> Add Attachments
    </label>
    <input type="file" id="attachments" name="attachments[]" multiple hidden accept=".jpg,.jpeg,.png,.webp"
        onchange="handleFileSelection(this)">
    <ul id="selectedFiles" class="list-unstyled mt-2"></ul>
</div>

<script>
    let selectedFiles = [];

    function handleFileSelection(input) {
        const fileList = Array.from(input.files);
        const selectedFilesList = $('#selectedFiles');

        // Validate and add new files
        for (const file of fileList) {
            if (selectedFiles.length >= 5) {
                alert('You can only upload up to 5 files.');
                break;
            }

            if (file.size > 2 * 1024 * 1024) { // 2MB limit
                alert(`File "${file.name}" exceeds the 2MB size limit.`);
                continue;
            }

            if (!selectedFiles.some(f => f.name === file.name)) {
                selectedFiles.push(file);
            }
        }

        // Update the UI
        selectedFilesList.empty();
        selectedFiles.forEach((file, index) => {
            const listItem = $(`
                <li class="attachment">
                    <span>${file.name}</span>
                    <button type="button" class="ms-2" onclick="removeFile(${index})">
                        <i class="bi bi-x"></i>
                    </button>
                </li>
            `);
            selectedFilesList.append(listItem);
        });

        // Reset the input to allow re-selection of the same file
        $(input).val('');
    }

    function removeFile(index) {
        selectedFiles.splice(index, 1);
        handleFileSelection({ files: [] }); // Refresh the UI
    }

    function clearFiles() {
        selectedFiles = [];
        $('#selectedFiles').empty();
    }
</script>