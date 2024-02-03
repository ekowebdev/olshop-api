<!DOCTYPE html>
<html>
<style>
    table,
    th,
    td {
        border: 0px !important;
    }

    @media only screen and (max-width: 600px) {
        .redirect-link {
        width: 370px !important;
        overflow: hidden;
        }
        .footer-email {
        padding-left: 2%;
        padding-right: 2%;
        }
    }
</style>

<body style="background-color: #FAFAFA; text-align: center; font-family: 'Nunito', Arial, Helvetica, sans-serif; padding: 0; margin: 0;">
  <table border="0" cellpadding="0" cellspacing="0" style="width:100%">
    <tr>
      <td bgcolor="#007D6E" height="120px"></td>
      <td bgcolor="#007D6E" height="120px" align="center" valign="center">
        <img src="http://13.229.99.87/images/mail/logo_horizontal.png" style="height: 60px;" />
      </td>
      <td bgcolor="#007D6E" height="120px"></td>
    </tr>
    <tr>
      <td bgcolor="#007D6E" height="10px"></td>
      <td bgcolor="#007D6E" width="680px" height="0px">
        <div
          style="width: auto; height: 100%; background: #ffffff; border-top-left-radius: 10px; border-top-right-radius: 10px; text-align: left; padding-top: 24px; padding-left: 24px; padding-right: 24px;">
          <div style="font-size: 18px; font-weight: 700; color: #272727;">
            Halo!
          </div>
          <div style="padding-top: 18px; padding-bottom: 0px; font-size: 14px; color: #272727; font-weight: 400; line-height: 19px;">
            Kami telah menerima permintaan untuk mengatur ulang kata sandi Anda. Silahkan klik tombol di bawah ini untuk melanjutkan:
          </div>
        </div>
      </td>
      <td bgcolor="#007D6E" height="0px"></td>
    </tr>
    <tr>
      <td height="360px"></td>
      <td width="680px" height="360px">
        <div class="content" style="width: auto; height: 100%; background: #ffffff; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px; box-shadow: 0px 3px 6px rgba(0, 0, 0, 0.12)">
          <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary" style="width: 100%; padding-left: 24px; padding-right: 24px;" width="100%">
            <tbody>
              <tr>
                <td style="padding-top: 34px; padding-bottom: 24px;">
                  <a href="https://bakti-shop.vercel.app/reset-password?email={{ $data['email']
                  }}&token={{ $data['token'] }}" target="_blank"
                    style="border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: normal; margin: 0; padding: 16px 16px; text-decoration: none; background-color: #007D6E; color: #ffffff;">Buat
                    kata sandi baru</a>
                </td>
              </tr>
              <tr>
                <td align="center" valign="center" style="padding-top: 16px; padding-bottom: 40px;">
                  <div class="redirect-link" style="width:640px; text-align: center; color: #272727; font-size: 14px; font-weight: 400; line-height: 19px; word-wrap: break-word;">
                    atau tempel alamat ini ke browser Anda: <a href="" style="font-size: 14px; color: blue;">http://13.229.99.87/reset-password?email={{ $data['email']
                      }}&token={{ $data['token'] }}</a>
                  </div>
                </td>
              </tr>
              <tr>
                <td>
                  <p style="font-size: 14px; color: #272727; text-align: left; margin-bottom: 34px;">
                    Link ini berlaku selama 1 jam. Hubungi kami jika Anda mengalami kesulitan mengatur ulang kata Sandi Anda.
                  </p>
                  <p style="text-align: left; font-size: 14px; color: #272727; margin-bottom: 24px;">
                    Salam,
                  </p>
                  <p style="text-align: left; font-size: 14px; color: #272727">
                    Tim Bakti Shop
                  </p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </td>
      <td height="360px"></td>
    </tr>
    <tr>
      <td height="60px"></td>
      <td height="60px">
        <div class="footer-email" style="margin-top: 10px;">
          <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary" style=" width: 100%;" width="100%">
            <tbody>
              <tr>
                <td style="text-align: left; padding-top: 18px; padding-bottom: 24px;">
                  <img src="http://13.229.99.87/images/mail/logo_footer_horizontal.png" style="height: 24px;" />
                </td>
                <td style="text-align: right; padding-top: 18px; padding-bottom: 24px;">
                  <span style="font-size: 16px; color: #333333; font-weight: 600;">&copy; {{ date('Y') }} </span>
                  <span style="font-size: 16px; color: #007D6E; font-weight: 600;">Bakti Shop</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </td>
      <td height="60px"></td>
    </tr>
  </table>
</body>

</html>