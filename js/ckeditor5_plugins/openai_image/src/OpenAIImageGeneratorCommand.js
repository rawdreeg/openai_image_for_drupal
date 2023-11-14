
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

    // Show loading indicator
    this._showLoadingIndicator(true);

    // Call the endpoint with the prompt.
    fetch('/openai-image/api/image/create' + `?prompt=${promptText}&size=1024x1024&response_format=b64_json`, {//options => (optional)
      method: 'GET',
    })
      .then(response => response.json())
      .then(data => {

        // Hide loading indicator
        this._showLoadingIndicator(false);

        // check error
        if (data.error) {
          console.error(data.error);
          // show error message
          alert("Image generation failed with the following error: \n\n" + data.error);

          return;
        }
        if (data && data.length) {
          // Provide UI for user to select an image.
          this._showImageSelectionUI(editor, data, promptText);
        } else {
          console.error('No images returned from the API');
        }
      })
      .catch(error => {
        console.error('Error:', error);
      });
  }

  _showImageSelectionUI(editor, images, promptText) {

    // Hide loading indicator
    this._showLoadingIndicator(false);

    // Create a container for the image selection UI
    const selectionContainer = document.createElement('div');
    selectionContainer.style.position = 'absolute';
    selectionContainer.style.top = '50%';
    selectionContainer.style.left = '50%';
    selectionContainer.style.transform = 'translate(-50%, -50%)';
    selectionContainer.style.backgroundColor = '#fff';
    selectionContainer.style.padding = '20px';
    selectionContainer.style.border = '1px solid #ccc';
    selectionContainer.style.zIndex = '1000';
    selectionContainer.style.maxHeight = '400px';
    selectionContainer.style.overflowY = 'auto';

    // Add a title
    const title = document.createElement('h3');
    title.innerText = 'Select an Image';
    selectionContainer.appendChild(title);

    // Function to handle image selection
    const selectImage = (base64Image) => {

      // sav image in Drupal
      fetch('/openai-image/api/image/save', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          image: base64Image,
          filename: promptText,
        }),
      }).then(response => response.json())
        .then(data => {

          // Handle error
          if (data.error) {
            console.error(data.error);
            // show error message
            alert("Image saving failed with the following error: \n\n" + data.error);

            return;
          }

          const imageUrl = data.url;
          editor.model.change(writer => {
            const imageElement = writer.createElement('imageBlock', {
              src: imageUrl,
              alt: promptText,
            });

            editor.model.insertContent(imageElement, editor.model.document.selection);
          });

          // Close the selection UI
          document.body.removeChild(selectionContainer);

        }).catch(error => {
          console.error('Error:', error);
          alert("There was an error saving your image.");
      });
    };

    images.forEach(image => {
      const img = document.createElement('img');
      img.src = 'data:image/png;base64,' + image.b64_json;
      img.style.width = '100px';
      img.style.height = '100px';
      img.style.margin = '10px';
      img.style.cursor = 'pointer';
      img.addEventListener('click', () => selectImage(image.b64_json));

      selectionContainer.appendChild(img);
    });

    // Append the selection container to the body
    document.body.appendChild(selectionContainer);

  }

  _showLoadingIndicator(show) {
    let loadingIndicator = document.getElementById('loading-indicator');
    if (!loadingIndicator) {
      loadingIndicator = document.createElement('div');
      loadingIndicator.id = 'loading-indicator';
      loadingIndicator.innerText = 'Loading images...';
      loadingIndicator.style.position = 'absolute';
      loadingIndicator.style.top = '50%';
      loadingIndicator.style.left = '50%';
      loadingIndicator.style.transform = 'translate(-50%, -50%)';
      loadingIndicator.style.backgroundColor = '#fff';
      loadingIndicator.style.padding = '20px';
      loadingIndicator.style.border = '1px solid #ccc';
      loadingIndicator.style.zIndex = '1000';
      loadingIndicator.style.maxHeight = '400px';
      loadingIndicator.style.overflowY = 'auto';
      document.body.appendChild(loadingIndicator);
    }
    loadingIndicator.style.display = show ? 'block' : 'none';
  }

}
