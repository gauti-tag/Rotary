document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("form-don");
  let errorMsg = "";
  let isValid = true;
  let formData;

  form.addEventListener("submit", function (event) {
    event.preventDefault();

    const name = document.getElementById("name");
    if (name.value.trim() === "") {
      isValid = false;
      errorMsg += "Le nom est requis.\n";
    }

    const email = document.getElementById("email");
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email.value.trim())) {
      isValid = false;
      errorMsg += "Veuillez entrer une adresse email valide.\n";
    }

    const phone = document.getElementById("phone");
    const phoneRegex = /^(\+225|00225)?[ -]?(\d{2}[ -]?){4}\d{2}$/;
    if (!phoneRegex.test(phone.value.trim())) {
      isValid = false;
      errorMsg += "Veuillez entrer un numéro de téléphone valide.\n";
    }

    const montant = document.getElementById("montant");
    if (montant.value.trim() === "" || parseInt(montant.value) <= 0) {
      isValid = false;
      errorMsg += "Veuillez entrer un montant valide.\n";
    }

    if (isValid) {
      formData = new FormData(form);
      console.log("Données soumises:", formData);
      for (let pair of formData.entries()) {
        console.log(pair[0] + ": " + pair[1]);
      }
      showModal(
        `Vous vous dirigez vers un paiement mobile de ${montant.value} en guise de contribution Solidaire pour une Côte d'Ivoire plus verte`
      );
    } else {
      alert(errorMsg);
    }
  });

  function showModal(message) {
    const modal = document.querySelector(".modal");
    const modalMessage = document.getElementById("modal-message");
    const modalOkBtn = document.getElementById("modal-ok-btn");

    modalMessage.textContent = message;
    modal.style.display = "block";

    modalOkBtn.addEventListener("click", function () {
      modalOkBtn.disabled = true; // Désactive le bouton OK pour éviter des clics multiples
      fetch("server/script.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((json) => {
          form.reset(); // Réinitialise le formulaire
          modal.style.display = "none"; // Ferme la modale
          console.log(json)
          window.open(json.url, "_blank").focus();
        })
        .catch((error) => {
          console.error("Erreur lors de la soumission du formulaire:", error);
          alert("Une erreur est survenue lors de la soumission du formulaire.");
        });
    });
  }

  const modalCloseBtn = document.querySelector(".modal-close-btn");
  modalCloseBtn.addEventListener("click", function () {
    const modal = document.querySelector(".modal");
    modal.style.display = "none";
  });
});
