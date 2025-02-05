document.getElementById('editGmailBtn').addEventListener('click', function() {
    document.getElementById('gmailForm').style.display = 'block';
    document.getElementById('editGmailBtn').style.display = 'none';
    document.getElementById('gmailText').style.display = 'none';
  });

  document.getElementById('cancelEdit').addEventListener('click', function() {
    document.getElementById('gmailForm').style.display = 'none';
    document.getElementById('editGmailBtn').style.display = 'inline-block';
    document.getElementById('gmailText').style.display = 'inline';
  });