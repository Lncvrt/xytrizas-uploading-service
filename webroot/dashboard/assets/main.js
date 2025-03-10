function logout() {
    document.cookie = "session=" + "; path=/";
    location.reload();
}

function showNotification(message, type) {
    var notification = document.createElement('div');
    notification.className = 'notification ' + type;
    notification.style.display = 'flex';
    notification.style.flexDirection = 'row';
    notification.style.alignItems = 'center';

    var iconDiv = document.createElement('div');
    var icon = document.createElement('i');
    icon.className = type === 'Success' ? 'fas fa-check' : 'fas fa-times';
    icon.style.color = type === 'Success' ? '#4CAF50' : '#f44336';
    iconDiv.appendChild(icon);
    notification.appendChild(iconDiv);

    var textDiv = document.createElement('div');
    textDiv.id = 'notification-message';
    textDiv.style.display = 'flex';
    textDiv.style.flexDirection = 'column';

    var titleNode = document.createElement('h4');
    titleNode.style.margin = '0';
    titleNode.appendChild(document.createTextNode(type));
    textDiv.appendChild(titleNode);

    var messageNode = document.createElement('div');
    messageNode.style.margin = '0';
    messageNode.appendChild(document.createTextNode(message));
    textDiv.appendChild(messageNode);

    notification.appendChild(textDiv);

    var container = document.getElementById('notification-container');
    container.appendChild(notification);
    notification.style.transform = 'translateY(100%)';
    setTimeout(function () {
        notification.style.transform = 'translateY(0)';
    }, 100);

    setTimeout(function () {
        notification.style.transform = 'translateY(100%)';
        setTimeout(function () {
            container.removeChild(notification);
        }, 500);
    }, 5000);
}

function showSuccess(message) {
    showNotification(message, 'Success');
}

function showError(message) {
    showNotification(message, 'Error');
}

document.addEventListener('DOMContentLoaded', (event) => {
    let element = "";

    element = document.getElementById('dropZone');
    if (element) {
        element.addEventListener('click', function () {
            document.getElementById('fileInput').click();
        });
    }

    element = document.getElementById('fileInput');
    if (element) {
        element.addEventListener('change', function (event) {
            handleFileUpload(event.target.files[0]);
        });
    }

    element = document.getElementById('dropZone');
    if (element) {
        element.addEventListener('dragover', function (event) {
            event.preventDefault();
            event.stopPropagation();
            event.target.style.border = '3px solid #09f';
        });
    }


    element = document.getElementById('dropZone');
    if (element) {
        element.addEventListener('dragleave', function (event) {
            event.preventDefault();
            event.stopPropagation();
            event.target.style.border = '2px dashed #fff';
        });
    }

    element = document.getElementById('dropZone');
    if (element) {
        element.addEventListener('drop', function (event) {
            event.preventDefault();
            event.stopPropagation();
            event.target.style.border = '2px dashed #fff';
            const files = event.dataTransfer.files;
            if (files.length) {
                handleFileUpload(files[0]);
            }
        });
    }

    element = document.getElementById('booleanSetting');
    if (element) {
        element.addEventListener('change', function () {
            let value = this.checked ? true : false;

            let label = document.querySelector("p[id='settingName']").innerText.toLowerCase().replace(/ /g, '-');

            $.ajax({
                url: '/api/dashboard/setPreferences.php',
                type: 'POST',
                data: { [`${label}`]: value },
                success: function (response) {
                    console.log('Full response:', response);
                    if (response.success) {
                        console.log('Settings updated: ', response.response);
                        showSuccess(response.response);
                    } else {
                        console.error('Update failed: ', response.response);
                        showError(response.response);
                    }
                },
                error: function (xhr) {
                    console.error('Update failed: ', xhr);
                    if (xhr.responseJSON && xhr.responseJSON.response) {
                        showError(xhr.responseJSON.response);
                    } else {
                        showError(xhr.responseText);
                    }
                }
            });
        });
    }

    element1 = document.getElementById('passwordChangeForm');
    element2 = document.getElementById('usernameChangeForm');
    if (element1 && element2) {
        $(document).ready(function () {
            $('#passwordChangeForm').on('submit', function (e) {
                e.preventDefault();
                var oldPassword = $('#oldPassword').val();
                var newPassword = $('#newPassword').val();
                var confirmPassword = $('#confirmPassword').val();
                var username = $('#username').val();
                $.ajax({
                    url: '/api/changePassword.php',
                    type: 'POST',
                    data: {
                        username: username,
                        oldpassword: oldPassword,
                        newpassword: newPassword,
                        confirmpassword: confirmPassword
                    },
                    success: function (data) {
                        response = JSON.parse(data);
                        if (response.success) {
                            location.reload();
                        } else {
                            showError(response.response);
                        }
                    },
                    error: function (error) {
                        if (error.responseJSON && error.responseJSON.response) {
                            showError(error.responseJSON.response);
                        } else {
                            showError(error);
                        }
                    }
                });
            });

            $('#usernameChangeForm').on('submit', function (e) {
                e.preventDefault();
                var oldUsername = $('#oldUsername').val();
                var newUsername = $('#newUsername').val();
                var password = $('#usernamepassword').val();
                $.ajax({
                    url: '/api/changeUsername.php',
                    type: 'POST',
                    data: {
                        oldusername: oldUsername,
                        newusername: newUsername,
                        password: password
                    },
                    success: function (data) {
                        response = data;
                        if (response.success) {
                            showSuccess(response.response);
                            $('#oldUsername').val(newUsername);
                            $('#username').html('<strong>Username:</strong> ').append(document.createTextNode(newUsername));
                        } else {
                            showError(response.response);
                        }
                    },
                    error: function (jqXHR) {
                        if (jqXHR.responseJSON && jqXHR.responseJSON.response) {
                            showError(jqXHR.responseJSON.response);
                        } else {
                            showError(jqXHR.responseText);
                        }
                    }
                });
            });
        });
    }
});

function deleteFile(deletionKey, imageId) {
    const deleteUrl = '/delete/' + deletionKey;

    $.ajax({
        url: deleteUrl,
        type: 'GET',
        dataType: 'json',
        contentType: 'application/json',
        success: function (responseJson) {
            console.log('Server response:', responseJson);

            const isSuccess = responseJson.success === true || responseJson.success === 'true';

            if (isSuccess) {
                const galleryItem = document.querySelector('.gallery-item[data-id="' + imageId + '"]');
                if (galleryItem) {
                    galleryItem.remove();
                } else {
                    console.error('Gallery item not found:', deletionKey);
                }
                console.log('File deleted successfully:', deletionKey);
                showSuccess("File deleted Successfuly")
            } else {
                console.error('Error deleting file:', responseJson.response);
                showError("Error deleting file: " + responseJson.response)
            }
        },
        error: function (xhr, status, error) {
            console.error('Error deleting file:', error + ' (' + status + ')');
            showError('Error deleting file: ' + error + ' (' + status + ')');
        }
    });
}

async function copyToClipboard(text, message, type) {
    if (!navigator.clipboard) {
        console.error('Clipboard not available');
        showError('Copying not supported on this browser.');
        return;
    }

    try {
        await navigator.clipboard.writeText(text);
        console.log('Link copied to clipboard:', text);

        if (message) {
            type = type.toString();
            if (type === "0" || type === "success") {
                showSuccess(message);
            } else if (type === "1" || type === "error") {
                showError(message);
            } else {
                console.log('Invalid copy type');
            }
        }
    } catch (err) {
        console.error('Failed to copy content:', err);
        showError('Failed to copy content: ' + err);
    }
}

function downloadFile(fileId, originalName, fileType) {
    $.ajax({
        url: 'https://files.upload.xytriza.com/' + fileId,
        method: 'GET',
        xhrFields: {
            responseType: 'blob'
        },
        success: function (data) {
            var blob = new Blob([data], { type: fileType });
            var url = window.URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = originalName;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
            showSuccess("File downloaded successfully")
        },
        error: function (xhr, status, error) {
            console.error('Error downloading file:', error + ' (' + status + ')');
            showError('Error downloading file: ' + error + ' (' + status + ')');
        }
    });
}

function formatDate(dateStr) {
    const months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    const date = new Date(dateStr);
    const year = date.getFullYear();
    const month = months[date.getMonth()];
    const day = date.getDate();
    return `${month} ${day}, ${year}`;
}

function formatAllDates(dateStrArray) {
    return dateStrArray.map(dateStr => formatDate(dateStr));
}

async function handleFileUpload(file) {
    let formData = new FormData();
    formData.append('file', file);

    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/api/uploadFile.php', true);

    document.getElementById('progressText').style.display = 'block';

    xhr.upload.onprogress = function (event) {
        if (event.lengthComputable) {
            const percentage = Math.round((event.loaded / event.total) * 100);
            document.getElementById('progressText').innerText = percentage + '%';
        } else {
            console.log('Progress event not computable');
        }
    };

    xhr.onload = function () {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success === true) {
                copyToClipboard(response.fileUrl, "", 0);
                showSuccess('File uploaded successfully. Link copied to clipboard');
            } else {
                showError(response.response);
            }
        } else {
            console.error('Error:', xhr.statusText);
            showError(xhr.statusText);
        }
        document.getElementById('progressText').style.display = 'none';
    };
    xhr.send(formData);
}

async function handleUrlUpload() {
    const url = document.getElementById('urlInput').value = document.getElementById('urlInput').value.replace('/files/', 'https://files.upload.xytriza.com/').replace('http://upload.xytriza.com/files/', 'https://files.upload.xytriza.com/');
    const fileName = document.getElementById('fileName').value;
    if (!url) {
        showError('Please enter a valid URL');
        return;
    }

    const formData = new FormData();

    try {
        const response = await fetch(url, { mode: 'cors' });
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const blob = await response.blob();
        const fileType = blob.type;

        const file = new File([blob], fileName, { type: fileType });

        formData.append('file', file);
    } catch (error) {
        showError('Unable to upload file. Make sure the target file has CORS enabled. Error: ' + error);
        console.error(error);
    }

    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/api/uploadFile.php', true);

    document.getElementById('progressText').style.display = 'block';

    xhr.upload.onprogress = function (event) {
        if (event.lengthComputable) {
            const percentage = Math.round((event.loaded / event.total) * 100);
            document.getElementById('progressText').innerText = percentage + '%';
        } else {
            console.log('Progress event not computable');
        }
    };

    xhr.onload = function () {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success === true) {
                copyToClipboard(response.fileUrl, ""), 0;
                showSuccess('File uploaded successfully. Link copied to clipboard');
            } else {
                showError(response.response);
            }
        } else {
            console.error('Error:', xhr.statusText);
            showError('An error occurred while uploading the file');
        }
        document.getElementById('progressText').style.display = 'none';
    };
    xhr.send(formData);
}

function downloadConfig() {
    $.ajax({
        url: '/api/dashboard/generateConfig.php',
        method: 'GET',
        success: function (response) {
            var downloadLink = document.createElement('a');
            var url = window.URL.createObjectURL(new Blob([JSON.stringify(response)]));
            downloadLink.href = url;
            downloadLink.download = 'xytrizas-uploading-service.sxcu';
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
            showSuccess('Config file downloaded successfully');
        },
        error: function (jqXHR, textStatus, errorThrown) {
            if (jqXHR.responseJSON && jqXHR.responseJSON.response) {
                showError(jqXHR.responseJSON.response);
            } else {
                showError(jqXHR.responseText);
            }
        }
    });
}

function setCookieAndRedirect(clientID, redirectUrl) {
    document.cookie = "redirect_link=" + window.location.href + "; expires=Session; path=/;";
    window.location.href = `https://discord.com/api/oauth2/authorize?client_id=${clientID}&redirect_uri=${redirectUrl}&response_type=code&scope=identify+guilds.join+email`;
}

function openFileSettings(fileId, filename, filepassword) {
    showError("Feature coming soon!");
    return
    var fileSettings = document.getElementById('file-settings');
    var fileSettingsPassword = document.getElementById('file-settings-password');
    var fileSettingsFilename = document.getElementById('file-settings-filename');
    var fileSettingsId = document.getElementById('file-settings-id');
    document.body.classList.add('tint');

    fileSettingsPassword.value = filepassword;
    fileSettingsFilename.value = filename;
    fileSettingsId.value = fileId;

    fileSettings.style.display = 'block';
}

function saveFileSettings() {
    var fileSettingsPassword = document.getElementById('file-settings-password');
    var fileSettingsFilename = document.getElementById('file-settings-filename');
    var fileSettingsId = document.getElementById('file-settings-id');
    var fileSettings = document.getElementById('file-settings');

    fileSettings.style.display = 'none';
    document.body.classList.remove('tint');

    $.ajax({
        url: '/api/filePreferences.php',
        type: 'POST',
        data: {
            fileId: fileSettingsId.value,
            password: fileSettingsPassword.value,
            filename: fileSettingsFilename.value
        },
        success: function (response) {
            console.log('Server response:', response);
            if (response.success) {
                showSuccess('File settings saved successfully');

                var galleryItem = document.querySelector('.gallery-item[data-id="' + fileSettingsId.value + '"]');
                if (galleryItem) {
                    var filenameElement = galleryItem.querySelector('a[target="_blank"] p');
                    if (filenameElement) {
                        filenameElement.textContent = fileSettingsFilename.value;
                    }

                    var settingsButton = galleryItem.querySelector('.fa-cog');
                    if (settingsButton) {
                        settingsButton.setAttribute('onclick', 'openFileSettings("' + fileSettingsId.value + '", "' + fileSettingsFilename.value + '", "' + fileSettingsPassword.value + '")');
                    }
                }
            } else {
                showError(response.response);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            showError(jqXHR.responseText);
        }
    });
}

function closeFileSettings() {
    var fileSettings = document.getElementById('file-settings');
    fileSettings.style.display = 'none';
    document.body.classList.remove('tint');

    var fileSettingsPassword = document.getElementById('file-settings-password');
    var fileSettingsFilename = document.getElementById('file-settings-filename');

    fileSettingsPassword.value = '';
    fileSettingsFilename.value = '';
}

function generateAPIKey() {
    $.ajax({
        url: '/api/dashboard/generateAPIKey.php',
        type: 'GET',
        success: function (response) {
            console.log('Server response:', response);
            if (response.success) {
                showSuccess('API key regenerated successfully');
            } else {
                showError(response.response);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            showError(jqXHR.responseText);
        }
    });
}

function copyAPIKey() {
    $.ajax({
        url: '/api/dashboard/getAPIKey.php',
        type: 'GET',
        success: function (response) {
            console.log('Server response:', response);
            if (response.success) {
                copyToClipboard(response.api_key, 'API Key copied to clipboard', 0);
            } else {
                showError(response.response);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            showError(jqXHR.responseText);
        }
    });
}

function deleteAllFiles() {
    $.ajax({
        url: '/api/deleteAllFiles.php',
        type: 'GET',
        success: function (response) {
            console.log('Server response:', response);
            if (response.success) {
                showSuccess('All files deleted successfully');
            } else {
                showError(response.response);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            showError(jqXHR.responseText);
        }
    });
}
