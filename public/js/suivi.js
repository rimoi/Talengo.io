//alert('test');

function onClickBtnFavoris(event) {
   event.preventDefault();

   const url = this.href;
   const button = this.querySelector('button')

   console.log(button);

   axios.get(url)
      .then(function (response) {
         // handle success
         //const favoris = response.data.favoris;
         if (button.classList.contains('btn-outline-danger')) {
            button.classList.replace('btn-outline-danger', 'btn-outline-success');
            button.textContent = "Suivre"
         }
         else{
            button.classList.replace('btn-outline-success', 'btn-outline-danger');
            button.textContent = "Ne plus suivre"
         }
            
         console.log(response);
      })
      .catch(function (error) {
         // handle error
         if (error.status === 403) {
            window.alert("Vous ne pouvez pas  ajouter un microservice en favoris sans pour autant vous connecter")
         }else{
            window.alert("Une erreur s'est produite lors de la requette veuillez resayer plus tard")
         }
         console.log(error);
      })
      .finally(function () {
         // always executed
      });
}

document.querySelectorAll('a.js-suivis').forEach(function (link) {

   link.addEventListener('click', onClickBtnFavoris)

})