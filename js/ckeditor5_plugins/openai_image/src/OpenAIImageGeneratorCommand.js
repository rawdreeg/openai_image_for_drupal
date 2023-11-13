
import { Command } from 'ckeditor5/src/core';
export default class OpenAIImageGeneratorCommand extends Command {
  execute() {
    const editor = this.editor;
    this._createImageModal(editor);
  }

  _createImageModal(editor) {
    // Open a modal dialog with a form to submit the prompt.
    // For simplicity, we use window.prompt, but you should create a modal with HTML.

    const promptText = window.prompt('Enter your image prompt for OpenAI:');
    if (!promptText) {
      return;
    }

    // Call the endpoint with the prompt.
    fetch('/openai-image/api/image/create' + `?prompt=${promptText}&n=1&size=1024x1024`, {//options => (optional)
      method: 'GET',
    })
      .then(response => response.json())
      .then(data => {

        // check error
        if (data.error) {
          console.error('No images returned from the API');
          return;
        }
        if (data && data.length) {
          // Provide UI for user to select an image.
          this._showImageSelectionUI(editor, data);
        } else {
          console.error('No images returned from the API');
        }
      })
      .catch(error => {
        console.error('Error:', error);
      });
  }

  _showImageSelectionUI(editor, images) {
    // Implement your UI logic here.
    // This could be a simple list of images, or a more complex UI with previews.

    // For demonstration purposes, we'll just take the first image's URL.
    const imageUrl = images[0].url; // Replace with UI logic to select an image

    editor.model.change(writer => {
      const imageElement = writer.createElement('imageBlock', {
        src: imageUrl
      });

      // Insert the image in the current selection location.
      editor.model.insertContent(imageElement, editor.model.document.selection);
    });
  }
}
