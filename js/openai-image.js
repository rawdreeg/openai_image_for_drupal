window.addEventListener("DOMContentLoaded", function() {
  let btn = document.querySelector("#openai-image-generate-images");
  btn.addEventListener("click", function(event) {

    // prevent the default form submission
    event.preventDefault();

    let imagesWrapper = document.querySelector("#openai-image-images");
    imagesWrapper.innerHTML = "";
    let attributes = {
      n: this.getAttribute('data-n'),
      prompt: document.querySelector('input.openai-image-prompt').value,
      size: this.getAttribute('data-size'),
    }

    // check if prompt is empty
    if (attributes.prompt == "") {
      alert("Please enter a prompt");
      return;
    }

    this.style.color = "green";
    fetch('/openai-image/api/image/create' + `?prompt=${attributes.prompt}&n=${attributes.n}&size=${attributes.size}`, {//options => (optional)
      method: 'get' //Get / POST / ...
    }).then(function(response) {
      return response.json();
    })
    .then(function(data) {
      data.forEach(function(item) {
        let imgWrapper = document.createElement("div");
        imgWrapper.classList.add("openai-image-image-wrapper");

        let img = document.createElement("img");
        img.src = item.url;

        // attach button to download image
        let buttonWrapper = document.createElement("div");
        buttonWrapper.classList.add("openai-image-download-button-wrapper");

        let button = document.createElement("button");
        button.innerHTML = "Attach image";
        button.classList.add("openai-image-download-button");

        imgWrapper.appendChild(img);
        imgWrapper.appendChild(button);
        imgWrapper.appendChild(buttonWrapper);

        document.querySelector("#openai-image-images").appendChild(imgWrapper);
      })
    })
      .catch(function(err) {
      console.log("Error:"+err);
    });
  });
});
