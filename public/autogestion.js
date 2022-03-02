(() => {
  const conditionField = (conditioner, conditionees) => {
    conditioner = document.querySelector(conditioner);
    if (!conditioner) return;

    const show = (selector) => {
      document
        .querySelector(selector)
        .closest(".fields > div")
        .style.removeProperty("display");
      document.querySelector(selector).removeAttribute("disabled");
    };
    const hide = (selector) => {
      document.querySelector(selector).closest(".fields > div").style.display =
        "none";
      document.querySelector(selector).setAttribute("disabled", true);
    };

    const conditionate = () => {
      const value = conditioner.value;
      const all = Object.values(conditionees).reduce((prev, curr) => {
        return prev.concat(curr);
      }, []);
      all.forEach(hide);
      let any = false;
      for (let [condition, conditionee] of Object.entries(conditionees)) {
        if (value == condition) {
          any = true;
          conditionee.forEach(show);
        }
      }
      if (!any && value != "" && conditionees.else) {
        conditionees.else.forEach(show);
      }
    };

    conditioner.addEventListener("change", conditionate);
    conditionate();
  };

  const setupPaymentsForm = () => {
    const form = document.querySelector("#pagos");

    if (!form) {
      return;
    }

    const publicApiKey = "df16c9dedd1c4df684abb8be4adcee27";
    //const publicApiKey = "96e7f0d36a0648fb9a8dcb50ac06d26096e7f0d36a0648fb9a8dcb50ac06d260";
    const urlSandbox = "https://live.decidir.com/api/v2";
    //Para el ambiente de desarrollo
    const decidir = new Decidir(urlSandbox);
    //Se indica la public API Key
    decidir.setPublishableKey(publicApiKey);
    decidir.setTimeout(5000); //timeout de 5 segundos
    //formulario
    //Asigna la funcion de invocacion al evento de submit del formulario
    form.addEventListener("submit", sendForm);
    //funcion para manejar la respuesta
    function sdkResponseHandler(status, response) {
      console.log(status);
      if (status != 200 && status != 201) {
        alert(
          "Por favor revisá que la información del titular y la tarjeta sean correctas."
        );
      } else {
        form.querySelector("[name=token_decidir]").value = response.id;
        form.querySelector("[name=tarjeta_bin]").value = response.bin;
        form.querySelector("input[type=submit]").setAttribute("disabled", true);
        form.querySelector("input[type=submit]").style.background = "lightgray";
        form.submit();
      }
    }
    //funcion de invocacion con sdk
    function sendForm(event) {
      event.preventDefault();
      decidir.createToken(form, sdkResponseHandler); //formulario y callback
      return false;
    }
    //..codigo...
  };

  const disableFieldsOnProfile = () => {
    const onProfilePage = window.location.href.match(
      /\/autogestion\/mi-perfil\//
    );
    if (!onProfilePage) return;

    document
      .querySelector(".fields #id_tipo_documento")
      .setAttribute("readonly", true);
    document.querySelector(".fields #documento").setAttribute("readonly", true);
  };

  const updateCityFields = () => {
    const provinceField = document.querySelector(".fields #id_provincia");
    const cityField = document.querySelector(".fields #id_localidad");
    if (!provinceField || !cityField) return;
    const options = [...cityField.querySelectorAll("option")];

    const toggleCities = () => {
      cityField.innerHTML = "";
      for (let option of options) {
        if (
          option.dataset.provinceId != provinceField.value &&
          option.value != ""
        ) {
        } else {
          cityField.appendChild(option);
        }
      }
    };

    provinceField.addEventListener("change", toggleCities);

    toggleCities();
  };

  const togglePasswords = () => {
    const togglePassword = (e) => {
      if (e.type === "password") e.type = "text";
      else e.type = "password";
    };
    const passwordFields = document.querySelectorAll(
      ".fields input[type=password]"
    );
    passwordFields.forEach((el) => {
      const button = document.createElement("button");
      button.type = "button";
      button.className = "toggle-password";
      button.innerHTML = '<i class="fas fa-eye"></i>';
      el.parentNode.appendChild(button);
      button.addEventListener("click", () => {
        togglePassword(el);
        button.innerHTML =
          button.innerHTML === '<i class="fas fa-eye"></i>'
            ? '<i class="fas fa-eye-slash"></i>'
            : '<i class="fas fa-eye"></i>';
      });
    });
  };

  window.addEventListener("load", () => {
    conditionField("[name=id_tipo_documento]", {
      5: ["[name=nombre]", "[name=apellido]"],
      2: ["[name=razon_social]"],
    });

    conditionField("[name=id_medio_de_pago]", {
      9: ["[name=nro_cbu]"],
      else: [
        "[name=nro_tarjeta]",
        "[name=vencimiento_tarjeta_ano]",
        "[name=vencimiento_tarjeta_mes]",
      ],
    });

    setupPaymentsForm();

    disableFieldsOnProfile();

    updateCityFields();

    togglePasswords();
  });
})();
