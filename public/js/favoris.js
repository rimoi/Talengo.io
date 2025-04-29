function onClickBtnFavoris(event) {
   event.preventDefault();

   const url = this.href;
   const icone = this.querySelector('i')

   console.log(icone);

   axios.get(url)
      .then(function (response) {
         // handle success
         //const favoris = response.data.favoris;
         if (icone.classList.contains('green')) {
            icone.classList.replace('green', 'light');
         }
         else {
            icone.classList.replace('light', 'green');
         }

         console.log(response);
      })
      .catch(function (error) {
         if (error.status === 403) {
            window.alert("Vous ne pouvez pas  ajouter un microservice en favoris sans pour autant vous connecter")
         } else {
            window.alert("Une erreur s'est produite lors de la requette veuillez resayer plus tard")
         }
         console.log(error);
      })
      .finally(function () {
         // always executed
      });
}

document.querySelectorAll('a.js-favoris').forEach(function (link) {

   link.addEventListener('click', onClickBtnFavoris)

})