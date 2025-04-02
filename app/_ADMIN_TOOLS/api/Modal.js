document.addEventListener("DOMContentLoaded", () => {
  const remainingRange = document.getElementById("remainingRange");
  const rangeValue = document.getElementById("rangeValue");
  const hiddenInput = document.getElementById("remaining_uses");

  const updateRangeValue = (val) => {
    rangeValue.textContent = val;
    hiddenInput.value = val;
  };

  remainingRange.addEventListener("input", (e) => {
    updateRangeValue(e.target.value);
  });


  updateRangeValue(remainingRange.value);
});