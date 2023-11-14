
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
    fetch('/openai-image/api/image/create' + `?prompt=${promptText}&size=1024x1024&response_format=b64_json`, {//options => (optional)
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

    // Create a loading indicator
    const loadingIndicator = document.createElement('div');
    loadingIndicator.innerText = 'Loading images...';
    loadingIndicator.style.textAlign = 'center';
    loadingIndicator.style.padding = '20px';

    // Initially, show the loading indicator
    selectionContainer.appendChild(loadingIndicator);

    // Function to handle image selection
    const selectImage = (base64Image) => {
      const imageUrl = 'data:image/png;base64,' + base64Image;
      editor.model.change(writer => {
        const imageElement = writer.createElement('imageBlock', {
          src: imageUrl,
          alt: promptText,
        });

        editor.model.insertContent(imageElement, editor.model.document.selection);
      });

      // Close the selection UI
      document.body.removeChild(selectionContainer);
    };

    // Replace the loading indicator with the actual images once they are ready
    const displayImages = () => {
      // Remove loading indicator
      selectionContainer.removeChild(loadingIndicator);

      // Create and append image elements to the selection container
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
    };

    // Simulate a delay to fetch images (replace this with your actual image fetching logic)
    setTimeout(() => {
      displayImages();  // Call this function once images are fetched
    }, 2000);  // Example delay of 2000ms (2 seconds)

    // Append the selection container to the body
    document.body.appendChild(selectionContainer);

  }

}
