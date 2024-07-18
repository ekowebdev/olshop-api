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

<body
    style="background-color: #FAFAFA; text-align: center; font-family: 'Nunito', Arial, Helvetica, sans-serif; padding: 0; margin: 0;">
    <table border="0" cellpadding="0" cellspacing="0" style="width:100%;">
        <tr>
            <td bgcolor="#2B00C8" height="120px"></td>
            <td bgcolor="#2B00C8" height="120px">
                <div style="color:#FAFAFA; font-family: poppines; display: flex; align-items: center; justify-content: center; filter: brightness(0) invert(1); gap: 5px;">
                    <img src="{{ config('services.cloudinary.path_url') }}/{{ config('services.cloudinary.folder') }}/images/logo_baktiweb" style="width: 60px; height: 60px;" />
                    <span style="font-size: 24px;">
                        Shop
                    </span>
                </div>
            </td>
            <td bgcolor="#2B00C8" height="120px"></td>
        </tr>
        <tr>
            <td bgcolor="#2B00C8" height="10px"></td>
            <td bgcolor="#2B00C8" width="680px" height="0px">
                <div
                    style="width: auto; height: 100%; background: #ffffff; border-top-left-radius: 10px; border-top-right-radius: 10px; text-align: left; padding-top: 24px; padding-left: 24px; padding-right: 24px;">
                    <div style="font-size: 18px; font-weight: 700; color: #272727;">
                        {{ trans('all.notification_birthday_title', ['name' => $profile['name']]) }}
                    </div>
                    <div
                        style="padding-top: 18px; padding-bottom: 30px; font-size: 14px; color: #272727; font-weight: 400; line-height: 19px;">
                        {{ trans('all.notification_birthday_text') }}
                    </div>
                </div>
            </td>
            <td bgcolor="#2B00C8" height="0px"></td>
        </tr>
        <tr>
            <td height="20px"></td>
            <td width="680px">
                <div class="content"
                    style="width: auto; height: 100%; background: #ffffff; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px; box-shadow: 0px 3px 6px rgba(0, 0, 0, 0.12)">
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary"
                        style="width: 100%; padding-left: 24px; padding-right: 24px;" width="100%">
                        <tbody>
                            <tr>
                                <td>
                                    <p style="text-align: left; font-size: 14px; color: #272727; margin-bottom: 30px;">
                                        {{ trans('all.regards') }},
                                    </p>
                                    <p style="text-align: left; font-size: 14px; color: #272727">
                                        {{ trans('all.team') }} {{ config('app.name') }}
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </td>
        </tr>
        <tr>
            <td height="100px"></td>
            <td>
                <div class="footer-email">
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary"
                        width="100%">
                        <tbody>
                            <tr>
                                <td style="text-align: right; padding-top: 18px; padding-bottom: 24px;">
                                  <span style="font-size: 16px; color: #333333; font-weight: 600;">&copy; {{ date('Y') }} </span>
                                  <span style="font-size: 16px; color: #2B00C8; font-weight: 600;">{{ config('app.name') }}</span>
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
