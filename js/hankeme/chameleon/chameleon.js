/**
* HANKEME
*
* NOTICE OF LICENSE
*
* This source file is exclusively published under the Open Software License (OSL 3.0)
* See http://opensource.org/licenses/osl-3.0.php for Details
* In case of questions regarding the use of this source file please
refer to the contact below.
*
* @project: [HANKEME Chameleon]
* @contact: info@hankeme.de
* @collaborators: [strohmeier]
*/

/* Based on magento core browser.js */


MediabrowserUtility = {
    openDialog: function(url, width, height, title) {
        if ($('browser_window') && typeof(Windows) != 'undefined') {
            Windows.focus('browser_window');
            return;
        }
        this.dialogWindow = Dialog.info(null, {
            closable:     true,
            resizable:    false,
            draggable:    true,
            className:    'magento',
            windowClassName:    'popup-window',
            title:        title || 'Insert File...',
            top:          50,
            width:        width || 950,
            height:       height || 600,
            zIndex:       1000,
            recenterAuto: false,
            hideEffect:   Element.hide,
            showEffect:   Element.show,
            id:           'browser_window',
            onClose: this.closeDialog.bind(this)
        });
        new Ajax.Updater('modal_dialog_message', url, {evalScripts: true});
    },
    closeDialog: function(window) {
        if (!window) {
            window = this.dialogWindow;
        }
        if (window) {
            // IE fix - hidden form select fields after closing dialog
            WindowUtilities._showSelect();
            window.close();
        }
    }
};

Mediabrowser = Class.create();
Mediabrowser.prototype = {
    targetElementId: null,
    contentsUrl: null,
    onInsertUrl: null,
    newFolderUrl: null,
    deleteFolderUrl: null,
    deleteFilesUrl: null,
    headerText: null,
    tree: null,
    currentNode: null,
    storeId: null,
    initialize: function (setup) {
        this.newFolderPrompt = setup.newFolderPrompt;
        this.newFilePrompt = setup.newFilePrompt;
        this.deleteFolderConfirmationMessage = setup.deleteFolderConfirmationMessage;
        this.deleteFileConfirmationMessage = setup.deleteFileConfirmationMessage;
        this.targetElementId = setup.targetElementId;
        this.contentsUrl = setup.contentsUrl;
        this.onInsertUrl = setup.onInsertUrl;
        this.onSaveUrl = setup.onSaveUrl;
        this.newFolderUrl = setup.newFolderUrl;
        this.newFileUrl = setup.newFileUrl;
        this.deleteFolderUrl = setup.deleteFolderUrl;
        this.deleteFilesUrl = setup.deleteFilesUrl;
        this.headerText = setup.headerText;
	this.onBackupUrl = setup.onBackupUrl;
    },
    setTree: function (tree) {
        this.tree = tree;
        this.currentNode = tree.getRootNode();
    },

    getTree: function (tree) {
        return this.tree;
    },

    selectFolder: function (node, event) {

        var targetEl = this.getTargetElement();

	if(targetEl.hasClassName('onduty')) {
		var confirmed = confirm('Are you sure? All unsaved Data will be overwritten.');
		if(!confirmed) return false;
		else {
			targetEl.value='';
			targetEl.removeClassName('onduty').hide();
			this.hideActionButtons();
		}
	}

        this.currentNode = node;
        this.hideFileButtons();
        this.activateBlock('contents');

        if(node.id == 'root') {
            this.hideElement('button_delete_folder');
        } else {
            this.showElement('button_delete_folder');
        }

        this.updateHeader(this.currentNode);
        this.drawBreadcrumbs(this.currentNode);

        this.showElement('loading-mask');
        new Ajax.Request(this.contentsUrl, {
            parameters: {node: this.currentNode.id},
            evalJS: true,
            onSuccess: function(transport) {
                try {
                    this.currentNode.select();
                    this.onAjaxSuccess(transport);
                    this.hideElement('loading-mask');
                    if ($('contents') != undefined) {
                        $('contents').update(transport.responseText);
                        $$('div.filecnt').each(function(s) {
                            Event.observe(s.id, 'click', this.selectFile.bind(this));
                            //Event.observe(s.id, 'dblclick', this.insert.bind(this));
                        }.bind(this));
                    }
                } catch(e) {
                    alert(e.message);
                }
            }.bind(this)
        });
    },

    selectFolderById: function (nodeId) {
        var node = this.tree.getNodeById(nodeId);
        if (node.id) {
            this.selectFolder(node);
        }
    },

    selectFile: function (event) {
        var div = Event.findElement(event, 'DIV');
        $$('div.filecnt.selected[id!="' + div.id + '"]').each(function(e) {
            e.removeClassName('selected');
        })
        div.toggleClassName('selected');
        if(div.hasClassName('selected')) {
            this.showFileButtons();
        } else {
            this.hideFileButtons();
        }
    },

    showFileButtons: function () {
        this.showElement('button_delete_files');
        this.showElement('button_insert_files');
        this.showElement('button_backup_files');
    },

    hideFileButtons: function () {
        this.hideElement('button_delete_files');
        this.hideElement('button_insert_files');
        this.hideElement('button_backup_files');
    },

    showActionButtons: function () {
        this.showElement('button_save_changes');
    },

    hideActionButtons: function () {
        this.hideElement('button_save_changes');
    },

    handleUploadComplete: function(files) {
        $$('div[class*="file-row complete"]').each(function(e) {
            $(e.id).remove();
        });
        this.selectFolder(this.currentNode);
    },

    backup: function(event) {
        var div;
        
            $$('div.selected').each(function (e) {
                div = $(e.id);
            });
        
        if ($(div.id) == undefined) {
            return false;
        }

        var params = {filename:div.id, node:this.currentNode.id, store:this.storeId};

        new Ajax.Request(this.onBackupUrl, {
            parameters: params,
            onSuccess: function(transport) {
                try {
                    this.onAjaxSuccess(transport);
		    alert(transport.responseText);
                } catch (e) {
                    alert(e.message);
                }
            }.bind(this)
        });

	this.selectFolder(this.currentNode);
    },

    insert: function(event) {
        var div;
        if (event != undefined) {
            div = Event.findElement(event, 'DIV');
        } else {
            $$('div.selected').each(function (e) {
                div = $(e.id);
            });
        }
        if ($(div.id) == undefined) {
            return false;
        }
        var targetEl = this.getTargetElement();

	if(targetEl.hasClassName('onduty')) {
		var confirmed = confirm('Are you sure? All unsaved Data will be overwritten.');
		if(!confirmed) return false;
	}

        if (! targetEl) {
            alert("Target element not found for content update");
            Windows.close('browser_window');
            return;
        }

        var params = {filename:div.id, node:this.currentNode.id, store:this.storeId};

        if (targetEl.tagName.toLowerCase() == 'textarea') {
            params.as_is = 1;
        }

        new Ajax.Request(this.onInsertUrl, {
            parameters: params,
            onSuccess: function(transport) {
                try {
                    this.onAjaxSuccess(transport);
                    if (this.getMediaBrowserOpener()) {
                        self.blur();
                    }
                    Windows.close('browser_window');
		    targetEl.show();
		    $$('div.active').each(function (e) {
                	e.removeClassName('active');
            	    });
		    $$('div.selected').each(function (e) {
                	e.addClassName('active');
            	    });
		    
		    this.showActionButtons();

                    if (targetEl.tagName.toLowerCase() == 'input') {

                        targetEl.value = transport.responseText;

                    } else {

			targetEl.value = transport.responseText;
			targetEl.addClassName('onduty');

                        if (varienGlobalEvents) {
                            varienGlobalEvents.fireEvent('tinymceChange');
                        }
                    }
                } catch (e) {
                    alert(e.message);
                }
            }.bind(this)
        });
    },

    save: function(event) {

        var div;
        if (event != undefined) {
            div = Event.findElement(event, 'DIV');
        } else {
            $$('div.selected').each(function (e) {
                div = $(e.id);
            });
        }
        if ($(div.id) == undefined) {
            return false;
        }

        var targetEl = this.getTargetElement();

        if (! targetEl) {
            alert("Source element not found for content save");
            Windows.close('browser_window');
            return;
        }

	if(targetEl.hasClassName('onduty')) {

		var params = {filename:div.id, node:this.currentNode.id, store:this.storeId, content:targetEl.value};

		if (targetEl.tagName.toLowerCase() == 'textarea') {
		    params.as_is = 1;
		}

		new Ajax.Request(this.onSaveUrl, {
		    parameters: params,
		    onSuccess: function(transport) {
		        try {
		            this.onAjaxSuccess(transport);
		            if (this.getMediaBrowserOpener()) {
		                self.blur();
		            }
		            Windows.close('browser_window');
			    targetEl.show();
			    
			    this.showActionButtons();

		            alert(transport.responseText);

		        } catch (e) {
		            alert(e.message);
		        }
		    }.bind(this)
		});
	}
    },

    /**
     * Find document target element in next order:
     *  in acive file browser opener:
     *  - input field with ID: "src" in opener window
     *  - input field with ID: "href" in opener window
     *  in document:
     *  - element with target ID
     *
     * return HTMLelement | null
     */
    getTargetElement: function() {
        if (typeof(tinyMCE) != 'undefined' && tinyMCE.get(this.targetElementId)) {
            if ((opener = this.getMediaBrowserOpener())) {
                var targetElementId = tinyMceEditors.get(this.targetElementId).getMediaBrowserTargetElementId();
                return opener.document.getElementById(targetElementId);
            } else {
                return null;
            }
        } else {
            return document.getElementById(this.targetElementId);
        }
    },

    /**
     * Return opener Window object if it exists, not closed and editor is active
     *
     * return object | null
     */
    getMediaBrowserOpener: function() {
         if (typeof(tinyMCE) != 'undefined'
             && tinyMCE.get(this.targetElementId)
             && typeof(tinyMceEditors) != 'undefined'
             && ! tinyMceEditors.get(this.targetElementId).getMediaBrowserOpener().closed) {
             return tinyMceEditors.get(this.targetElementId).getMediaBrowserOpener();
         } else {
             return null;
         }
    },

    newFolder: function() {
        var folderName = prompt(this.newFolderPrompt);
        if (!folderName) {
            return false;
        }
        new Ajax.Request(this.newFolderUrl, {
            parameters: {name: folderName},
            onSuccess: function(transport) {
                try {
                    this.onAjaxSuccess(transport);
                    if (transport.responseText.isJSON()) {
                        var response = transport.responseText.evalJSON()
                        var newNode = new Ext.tree.AsyncTreeNode({
                            text: response.short_name,
                            draggable:false,
                            id:response.id,
                            expanded: true
                        });
                        var child = this.currentNode.appendChild(newNode);
                        this.tree.expandPath(child.getPath(), '', function(success, node) {
                            this.selectFolder(node);
                        }.bind(this));
                    }
                } catch (e) {
                    alert(e.message);
                }
            }.bind(this)
        })
    },

    deleteFolder: function() {
        if (!confirm(this.deleteFolderConfirmationMessage)) {
            return false;
        }
        new Ajax.Request(this.deleteFolderUrl, {
            onSuccess: function(transport) {
                try {
                    this.onAjaxSuccess(transport);
                    var parent = this.currentNode.parentNode;
                    //parent.removeChild(this.currentNode);
                    this.selectFolder(parent);
		    alert(transport.responseText);
                }
                catch (e) {
                    alert(e.message);
                }
            }.bind(this)
        })
    },

    createFile: function() {
        var fileName = prompt(this.newFilePrompt);
        if (!fileName) {
            return false;
        }

	var params = {name:fileName, node:this.currentNode.id, store:this.storeId};

        new Ajax.Request(this.newFileUrl, {
            parameters: params,
            onSuccess: function(transport) {
                try {
                    this.onAjaxSuccess(transport);
        	    this.selectFolder(this.currentNode);
		    alert(transport.responseText);
                } catch (e) {
                    alert(e.message);
                }
            }.bind(this)
        })
    },

    deleteFiles: function() {
        if (!confirm(this.deleteFileConfirmationMessage)) {
            return false;
        }
        var ids = [];
        var i = 0;
        $$('div.selected').each(function (e) {
            ids[i] = e.id;
            i++;
        });

	var params = {files: Object.toJSON(ids), node:this.currentNode.id, store:this.storeId};

        new Ajax.Request(this.deleteFilesUrl, {
            parameters: params,
            onSuccess: function(transport) {
                try {
                    this.onAjaxSuccess(transport);
                    this.selectFolder(this.currentNode);
		    alert(transport.responseText);
                } catch(e) {
                    alert(e.message);
                }
            }.bind(this)
        });
    },

    drawBreadcrumbs: function(node) {
        if ($('breadcrumbs') != undefined) {
            $('breadcrumbs').remove();
        }
        if (node.id == 'root') {
            return;
        }
        var path = node.getPath().split('/');
        var breadcrumbs = '';
        for(var i = 0, length = path.length; i < length; i++) {
            if (path[i] == '') {
                continue;
            }
            var currNode = this.tree.getNodeById(path[i]);
            if (currNode.id) {
                breadcrumbs += '<li>';
                breadcrumbs += '<a href="#" onclick="MediabrowserInstance.selectFolderById(\'' + currNode.id + '\');">' + currNode.text + '</a>';
                if(i < (length - 1)) {
                    breadcrumbs += ' <span>/</span>';
                }
                breadcrumbs += '</li>';
            }
        }

        if (breadcrumbs != '') {
            breadcrumbs = '<ul class="breadcrumbs" id="breadcrumbs">' + breadcrumbs + '</ul>';
            $('content_header').insert({after: breadcrumbs});
        }
    },

    updateHeader: function(node) {
        var header = (node.id == 'root' ? this.headerText : node.text);
        if ($('content_header_text') != undefined) {
            $('content_header_text').innerHTML = header;
        }
    },

    activateBlock: function(id) {
        //$$('div [id^=contents]').each(this.hideElement);
        this.showElement(id);
    },

    hideElement: function(id) {
        if ($(id) != undefined) {
            $(id).addClassName('no-display');
            $(id).hide();
        }
    },

    showElement: function(id) {
        if ($(id) != undefined) {
            $(id).removeClassName('no-display');
            $(id).show();
        }
    },

    onAjaxSuccess: function(transport) {
        if (transport.responseText.isJSON()) {
            var response = transport.responseText.evalJSON()
            if (response.error) {
                throw response;
            } else if (response.ajaxExpired && response.ajaxRedirect) {
                setLocation(response.ajaxRedirect);
            }
        }
    }
}
