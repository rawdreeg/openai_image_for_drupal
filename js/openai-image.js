(function ($, Drupal) {
  "use strict";
  var openaiImage = window.openaiImage = {};

  Drupal.behaviors.openaiImage = {
    attach: function (context, settings) {
      let btn = document.querySelector("#openai-image-generate-images");

      if ( !btn ) {
        return;
      }

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
        if (attributes.prompt === "") {
          alert("Please enter a prompt");
          return;
        }

        this.style.color = "green";

        this.innerHTML = "Generating images...";

        fetch('/openai-image/api/image/create' + `?prompt=${attributes.prompt}&n=${attributes.n}&size=${attributes.size}`, {//options => (optional)
          method: 'get' //Get / POST / ...
        }).then(function(response) {
          btn.innerHTML = "Generate images";
          return response.json();
        })
          .then(function(data) {

            // check error
            if (data.error) {
              imagesWrapper.innerHTML = "Image generation failed. Please try again." + "<br><br> <p style='color: red'>" + data.error + "</p>";
              btn.style.color = "";
              btn.innerHTML = "Generate images";
              return;
            }

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

            // reset button color and text
            btn.style.color = "";
            document.querySelector('input.openai-image-prompt').value = "";
          })
          .catch(function(err) {
            // log error
            console.log(err);

          });
      });

    }
  }

})(jQuery, Drupal);

